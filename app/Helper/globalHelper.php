<?php

use PDF as MPDF;
use App\Models\Page;
use App\Models\Media;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Learner;
use App\Models\Dropdown;
use App\Models\Wishlist;
use App\Models\Countries;
use App\Models\CoursePlan;
use App\Models\UserCourse;
use App\Models\CouponUsage;
use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\RatingReview;
use Illuminate\Support\Carbon;
use NumberToWords\NumberToWords;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\NotificationController;

/**
 * array to string conversion
 * @return String||Blank array
 */
if (!function_exists('arrayToString')) {
    function arrayToString($array = [])
    {
        if (isset($array) && !empty($array) && count($array) > 0) {

            return implode(',', $array);
        }
        return $array;
    }
}

function allowWhiteSpace($value)
{
    // dd($value)
    return preg_replace('/^\s+|\s+$|\s+(?=\s)/', '', $value);
}

/**
 * string to array conversion
 * @return Array
 */
if (!function_exists('stringToArray')) {
    function stringToArray($string = null)
    {
        if (isset($string) && !empty($string)) {

            return explode(',', $string);
        }
        // dd($string);
        return $string;
    }
}

if (!function_exists('isLearnerLoggedIn')) {
    function isLearnerLoggedIn()
    {
        return auth()->guard('learner')->check() ? true : false;
    }
}

if (!function_exists('authLearnerID')) {
    function authLearnerID()
    {
        return auth()->guard('learner')->check() ? auth()->guard('learner')->id() : null;
    }
}

function separateCountryCodeAndNumber($fullNumber)
{

    $countryCodes = Countries::get()->pluck("phonecode", "code")->toArray();

    $fullNumber = ltrim($fullNumber, '+');
    foreach ($countryCodes as $country => $code) {
        if (strpos($fullNumber, $code) === 0) {
            return [
                'country' => $country,
                'code' => $code,
                'mobile' => substr($fullNumber, strlen($code)),
            ];
        }
    }
    return null;
}

/**
 * @return Image if exists OR default image display if Image not found in record.
 * @param $imageURL(required), $defaultImageURL (optional)
 * If call this function without passing any parameter then default image will be display.
 */
if (!function_exists('getImageIfExists')) {
    function getImageIfExists($imageURL = null, $defaultImageURL = null)
    {
        // dd($imageURL);
        if (isset($imageURL) && !empty($imageURL) && Storage::exists($imageURL)) {
            return Storage::url($imageURL);
        } else {
            if (isset($defaultImageURL) && !empty($defaultImageURL)) {
                return $defaultImageURL;
            }
        }
        return URL::asset('front/img/default/Courses-placeholder.jpg');
    }
}
if (!function_exists('favicon_default')) {
    function favicon_default()
    {
        return asset('front/img/logo.svg');
    }
}

if (!function_exists('logo_default')) {
    function logo_default()
    {
        return asset('front/img/lifegurukul_small_logo.svg');
    }
}

if (!function_exists('course_img_default')) {
    function course_img_default()
    {
        return asset('front/img/default/Courses-placeholder.jpg');
    }
}
if (!function_exists('default_category_img')) {
    function default_category_img()
    {
        return asset('front/img/default/category-placeholder.jpg');
    }
}
if (!function_exists('blog_img_default')) {
    function blog_img_default()
    {
        return asset('front/img/default/Bolg-placeholder.jpg');
    }
}

if (!function_exists('user_img_default')) {
    function user_img_default()
    {
        return asset('front/img/user-pic.jpg');
    }
}

if (!function_exists('package_default_img')) {
    function package_default_img($id = null)
    {
        return asset('front/img/blog/blog-01.jpg');
    }
}
if (!function_exists('active_wishlist')) {
    function active_wishlist($courseId)
    {
        $background = '';
        if (Auth::guard('learner')->check() && isset($courseId) && !empty($courseId)) {
            if (Wishlist::where("course_id", $courseId)->where("learner_id", Auth::guard('learner')->id())->whereNull('deleted_at')->exists()) {
                $background = 'color-active';
            }
        }
        return $background;
    }
}

/**
 * Get Maximum price from 'course_plans' table (column:- final_payable_price)
 */
if (!function_exists('maxCoursePrice')) {
    function maxCoursePrice()
    {
        $courses = Course::with('plans')->get();
        $maxPrice = $courses->pluck('plans')->collapse()->pluck('final_payable_price')->max();
        // dd($maxPrice);
        return $maxPrice;
    }
}

/**
 * Frontend > Courses listing price filter
 */
if (!function_exists('coursePriceFilter')) {
    function coursePriceFilter()
    {
        $max_price = maxCoursePrice();
        $temp3 = [];
        if (!empty($max_price)) {
            $temp = 1;
            $temp += round($max_price / 2500, 0);
            //  $temp += substr($max_price, 0, 1);
            // dd($temp);
            $next = 0;
            if ($max_price > 500 && $max_price < 1250) {
                $temp += 1;
            }
            for ($i = 0; $i < $temp; $i++) {
                if ($i == 0) {
                    $temp3[$i]['left'] = 0;
                    $temp3[$i]['right'] = 500;
                    $next = $temp3[$i]['right'];
                } else {
                    if ($next >= 10000) {
                        $temp -= 1;
                        $temp3[$i]['left'] = 0;
                        if (!isset($temp3[$i]['right'])) {
                            break;
                        } else {
                            $temp3[$i]['right'] = $temp3[$i]['right'] + 5000;
                        }
                    } else {
                        // dump("1");
                        // $temp -= 1;
                        $next = ((500 * 2) * $i);
                        $temp3[$i]['left'] = 0;
                        $temp3[$i]['right'] = $next;
                    }
                }
            }
            // $key = 0;
            // $temp3 = [];
            // $temp4 = [];
            // for($i=1; $i <=$temp; ++$i) {
            //     if($i==1){
            //         $temp3[] = 500 * $i;
            //     } else {
            //         $key = $key +1;
            //         $temp3[] = 500 * $key;

            //     }
            //     $key++;
            // }
            // foreach($temp3 as $key=>$val) {
            //     $temp4[$key]['left'] = 0;
            //     $temp4[$key]['right'] = $val;
            // }
            //     // dd("");

        }
        // dd($temp4);
        return $temp3;
    }
}

/**
 * Back Button for admin panel
 */
if (!function_exists('redirect_to_back')) {
    function redirect_to_back($url = '')
    {
        if (isset($url) && !empty($url)) {
            $back = $url;
            return view('admin.layouts.partials.buttons.back', compact('back'))->render();
        }
        return view('admin.layouts.partials.buttons.back', ['back' => URL::previous()])->render();
    }
}

/**
 * This function is used for sorting 'Created At' column that will display on every module's listing page (Oldest->Latest && Latest->Oldest).
 */
if (!function_exists('created_at_hidden')) {
    function created_at_hidden($createdAt)
    {
        return !is_null($createdAt) ? '<span class="d-none">' . date('Y-m-d', strtotime($createdAt)) . '</span>' : '';
    }
}

/**
 * This function is used for sorting 'Created At' column that will display on every module's listing page (Oldest->Latest && Latest->Oldest).
 */
if (!function_exists('get_coupon_by_learner')) {
    function get_coupon_by_learner($learner_id, $course_id)
    {
        $current_date = date('Y-m-d');

        // $coupons = Coupon::whereJsonContains('courses', $course_id)
        //                 ->where('expiry_date', '>=', $current_date)
        //                 ->where('status', 1)
        //                 ->get();

        // $coupons = $coupons->reject(function ($coupon) use ($learner_id, $course_id) {
        //     $couponUsage = CouponUsage::where('learner_id', $learner_id)
        //                                 ->where('course_id', $course_id)
        //                                 ->where('coupon_id', $coupon->id)
        //                                 ->where('is_used', 1)
        //                                 ->exists();
        //     return $couponUsage;
        // });

        // $coupons->each(function ($coupon) {
        //     unset($coupon->courses);
        // });

        $coupons = Coupon::with(['getCouponCourse' => function ($q) use ($course_id) {
            $q->where('course_id', $course_id);
        }])->whereHas("getCouponCourse", function ($q) use ($course_id) {
            $q->where('course_id', $course_id);
        })
            ->where('expiry_date', '>=', $current_date)
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->get();

        $filteredCoupons = $coupons->reject(function ($coupon) use ($learner_id, $course_id) {
            $couponUsageExists = CouponUsage::where('learner_id', $learner_id)
                ->where('course_id', $course_id)
                ->where('coupon_id', @$coupon->getCouponCourse[0]->id)
                ->where('is_used', 1)
                ->exists();
            unset($coupon->getCouponCourse);
            return $couponUsageExists;
        });

        if ($filteredCoupons->isEmpty()) {
            $coupons = collect([]);
        } else {
            $coupons = $filteredCoupons;
        }

        return $coupons = $coupons->values();

        // $hasUsedAnyCoupon = CouponUsage::where('learner_id', $learner_id)
        //     ->where('is_used', 1)
        //     ->exists();

        // $coupons = $coupons->reject(function ($coupon) use ($learner_id, $course_id, $hasUsedAnyCoupon) {

        //     $couponUsage = CouponUsage::where('learner_id', $learner_id)
        //         ->where('course_id', $course_id)
        //         ->where('coupon_id', $coupon->getCouponCourse->where("coupon_id",$coupon->id)->first()->id)
        //         ->where('is_used', 1)
        //         ->get();

        //         if($coupon->new_user) {
        //             $user_course = UserCourse::where("learner_id",$learner_id)->where("course_id",$course_id)->first();
        //             if($user_course==null) {
        //                 return $couponUsage || ($hasUsedAnyCoupon);
        //             } else {
        //                 return collect([]);
        //             }
        //         } else {
        //             return $couponUsage || ($hasUsedAnyCoupon );
        //         }

        // });

        // $coupons->each(function ($coupon) {
        //     unset($coupon->courses);
        // });

        // return $coupons = $coupons->values();
    }
}

if (!function_exists('add_new_category_button')) {
    function add_new_category_button($slug = null)
    {
        $url = "javascript:void(0)";
        if (!is_null($slug)) {
            $id = Dropdown::whereSlug($slug)->firstOrFail()->id;
            if (!is_null($id)) {
                $url = url('backoffice/dropdown_options/' . $id . '/create');
            }
            return '<a href="' . $url . '" target="_blank" class="btn btn-secondary px-2 py-1" title="Add New Category" style="font-size:60%!important"><i class="fas fa-plus"></i></a>';
        }
        return '';
    }
}
if (!function_exists('re')) {
    function re($arr)
    {
        echo "<pre>";
        print_r($arr);
        echo "</pre>";
        exit;
    }
}
if (!function_exists('r')) {
    function r($arr)
    {
        echo "<pre>";
        print_r($arr);
        echo "</pre>";
    }
}

// if (!function_exists('check_course_is_free_or_not')) {
//     /**
//      * total_no_of_plan = 0 -> Free Course
//      * total_no_of_plan = 1 -> Single Plan
//      * total_no_of_plan > 1 -> Multiple Plans
//      */
//     function check_course_is_free_or_not($courseId = null, $pageType = 0)
//     {
//         $array = [];
//         $isLogin = auth()->guard('learner')->check() ? 0 : 1;
//         if (!is_null($courseId)) {
//             $course = Course::with(['plans' => function ($query) {
//                 $query->where('status', 1);
//             }])->withCount(['plans as planCount' => function ($query) {
//                 $query->where('status', 1);
//             }])->find($courseId);
//             $add = 1;
//             if (isset($course) && !empty($course)) {
//                 $date = new DateTime();
//                 $currentDate = $date->format('Y-m-d');
//                 $userCourse = UserCourse::where('learner_id', auth()->guard('learner')->user()->id ?? NULL)
//                     ->where('course_id', $courseId)
//                     ->where('order_status', 1)
//                     ->latest()
//                     ->first();
//                 if (isset($userCourse) && !empty($userCourse)) {
//                     if (strtotime($userCourse->expire_at) >= strtotime($currentDate) || empty($userCourse->expire_at)) {
//                         $array['is_login'] = $isLogin;
//                         $array['btn_text'] = 'Continue';
//                         $array['plan_number'] = 3; // already purchased
//                         $array['courseId'] = $courseId;
//                         $array['planUrl'] = route('course.preview', ['slug' => $course->slug]);
//                         $array['is_purchased'] = 1;
//                         $add = 0;
//                     } else {
//                         $add = 1;
//                     }
//                 }

//                 if ($add == 1) {
//                     if ($course->planCount == 1) {
//                         if ($course->plans[0]->plan_type == CoursePlan::PLAN_FREE) {
//                             $array['is_login'] = $isLogin;
//                             $array['btn_text'] = 'Add';
//                             $array['planUrl'] = route('checkout.free.plan', ['planId' => Crypt::encrypt($course->plans[0]->id)]);
//                             $array['courseId'] = $courseId;
//                             $array['plan_number'] = 0;
//                             $array['is_purchased'] = 0;
//                         } else {
//                             $array['btn_text'] = 'Buy Now';
//                             $array['is_login'] = $isLogin;
//                             $array['courseId'] = $courseId;
//                             $array['plan_number'] = 1; // single plan
//                             $array['planUrl'] = route('checkout.index', ['planId' => Crypt::encrypt($course->plans[0]->id)]);
//                             $array['is_purchased'] = 0;
//                         }
//                     } elseif ($course->planCount > 1) {
//                         $array['btn_text'] = 'Buy Now';
//                         $array['is_login'] = $isLogin;
//                         $array['courseId'] = $courseId;
//                         $array['plan_number'] = 2; // multiple plan
//                         $array['planUrl'] = route('course.all.plan', [$courseId]);
//                         $array['is_purchased'] = 0;
//                     }
//                 }
//             }
//         }
//         return  by_now_button($array, $pageType);
//         //  return $array;
//     }
// }

if (!function_exists('check_course_is_free_or_not')) {
    /*******
     * $pageType = 1 // homepage
     * $pageType = 2 //details Page of course and package page
     *******/
    function check_course_is_free_or_not($priceData = [], $pageType = 0)
    {
        // $expiredAt = get_plan_expire(141);
        // dd($expiredAt);
        $array = [];
        $add = 1;
        $isLogin = auth()->guard('learner')->check() ? 0 : 1;
        if (empty($priceData)) {
            return false;
        }

        try {
            if (!empty($priceData)) {

                // $planCnt = CoursePlan::where('status', 1)
                //                     ->where("course_id",$priceData['courseId'])
                //                     ->count();

                $course = Course::select('courses.id', 'courses.title')
                    ->where('courses.id', $priceData['courseId'])
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
                $planCnt = $course->plans->count();

                $date = new DateTime();
                $currentDate = $date->format('Y-m-d');
                $userCourse = UserCourse::where('learner_id', auth()->guard('learner')->user()->id ?? null)
                    ->where('course_id', $priceData['courseId'])
                    ->where(function ($query) {
                        $query->where('order_status', 1)
                            ->orWhere('order_status', 3)
                            ->orWhere('order_status', 4);
                    })
                    ->latest()
                    ->first();

                // For package
                $allUserCourses = UserCourse::with(['coursePackages'])
                    ->whereHas('course', function ($query) {
                        $query->where('type', 2);
                    })
                    ->where('order_status', "!=", 2)
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

                $isExistInPackage = 0;
                $expireDate = '';
                if (isset($data) && !empty($data)) {
                    foreach ($data as $k => $value) {
                        if (in_array($priceData['courseId'], $value['course_ids'])) {
                            $expireDate = $value['expire_at'];
                            $isExistInPackage = 1;
                            break;
                        }
                    }
                }

                if ($isExistInPackage == 1 && isUserCourseExpired($expireDate) == true && $priceData['type'] == 1) {
                    $array['is_login'] = $isLogin;

                    $array['btn_text'] = 'Play';
                    $array['plan_number'] = 3; // already purchased
                    $array['courseId'] = $priceData['courseId'];
                    if ($priceData['type'] == 1) {
                        $array['planUrl'] = route('course.preview.slug', ['slug' => $priceData['slug']]);
                    } else {
                        $array['planUrl'] = route('my.package.course', ['userCourseId' => $userCourse->id]);
                    }
                    $array['is_purchased'] = 1;
                    $add = 0;
                }
                //   else if (isset($userCourse) && !empty($userCourse)) {

                else if (isset($userCourse->course_id) && isUserCourseExpired($userCourse->expire_at) == true && $userCourse->course_id == $priceData['courseId']) {
                    // if($userCourse->course_id == 40) {
                    //     if(isUserCourseExpired($userCourse->expire_at)){
                    //         dd($userCourse->expire_at);
                    //     }
                    // }

                    $array['is_login'] = $isLogin;
                    $array['btn_text'] = 'Play';
                    $array['plan_number'] = 3; // already purchased
                    $array['courseId'] = $priceData['courseId'];
                    if ($priceData['type'] == 1) {
                        $array['planUrl'] = route('course.preview.slug', ['slug' => $priceData['slug']]);
                    } else {
                        $array['planUrl'] = route('my.package.course', ['userCourseId' => $userCourse->id]);
                    }
                    $array['is_purchased'] = 1;
                    $add = 0;

                    // }
                    // else {
                    //     $add = 1;
                    // }
                }
                if ($add == 1) {
                    if ($priceData['plan_type'] == 0) { // free
                        $array['is_login'] = $isLogin;
                        $array['btn_text'] = 'Add';
                        $array['is_purchased'] = 0;
                        if (isset($planCnt) && $planCnt == 1) {
                            $array['plan_number'] = 0;
                            $array['planUrl'] = route('checkout.free.plan', [
                                'planId' => Crypt::encrypt($priceData['planId']), "updated_new_coin" => 0,
                                "coupon_id" => 0,
                            ]);
                            // $array['planUrl'] = route('course.details', ['slug' => $priceData['slug']]);

                        } else {
                            $array['plan_number'] = 2; // multiple plan
                            $array['planUrl'] = route('course.all.plan', [$priceData['courseId']]);
                        }
                    }
                    if ($priceData['plan_type'] == 2 || $priceData['plan_type'] == 1) { // recuring
                        $array['is_login'] = $isLogin;
                        $array['btn_text'] = 'Buy Now';
                        $array['is_purchased'] = 0;
                        // if (isset($planCnt) && $planCnt == 1) {
                        //     $array['plan_number'] = 1;
                        //     $array['planUrl'] = route('checkout.index', ['planId' => Crypt::encrypt($priceData['planId'])]);
                        //     // $array['planUrl'] = route('course.details', ['slug' => $priceData['slug']]);
                        // } else {
                        //     $array['plan_number'] = 2; // multiple plan
                        //     $array['planUrl'] = route('course.all.plan', [$priceData['courseId']]);
                        // }
                        $array['plan_number'] = 2; // multiple plan
                        $array['planUrl'] = route('course.all.plan', [$priceData['courseId']]);
                    }
                }
            }
            // dd($array);
            return by_now_button($array, $pageType);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
if (!function_exists('by_now_button')) {
    function by_now_button($array = [], $pageType = 0)
    {
        /*******
         * $pageType = 1 // homepage
         * $pageType = 2 //list
         * $pageType = 3 //Individual
         * ********/
        $button = '';
        $priceModel = '';
        if (!empty($array)) {
            $mobileLogin = $array['is_login'] == 1 ? "mobileLogin" : "";
            if ($array['plan_number'] == 2) { // more than one Plan
                $planUrl = $array['planUrl'];
                $priceModel = "price_modal";
                $link = "href='javascript:void(0)' data-bs-toggle='modal' data-url='$planUrl'";
            } else if ($array['plan_number'] == 0) { // free plan
                $link = 'href="' . $array['planUrl'] . '"';
            } else if ($array['plan_number'] == 1) { // Only One plan and not Free
                $link = 'href="' . $array['planUrl'] . '"';
            } else if ($array['plan_number'] == 3) { // already purchased
                $link = 'href="' . $array['planUrl'] . '"';
            } else {
                $link = 'href="#"';
            }

            // if not logged set link to #
            if ($mobileLogin != '') {
                $link = 'href="#"';
                $priceModel = '';
            }

            if ($pageType == 1) // HomePage
            {
                $button = '<a class="btn btn-primary ' . $mobileLogin . ' ' . $priceModel . '" ' . $link . '>' . $array['btn_text'] . '</a>';
            }
            if ($pageType == 2) //list
            {
                $button = '<a  class="btn btn-primary ' . $mobileLogin . ' ' . $priceModel . '" ' . $link . '>' . $array['btn_text'] . '</a>';
            }
            if ($pageType == 3) //Individual
            {
                $button = '<a class="btn btn-enroll w-50 ' . $mobileLogin . ' ' . $priceModel . '" ' . $link . '>' . $array['btn_text'] . '</a>';
            }
        }
        echo $button;
    }
}

if (!function_exists('get_course_plan_price')) {
    function get_course_plan_price($planId = null)
    {
        $final_payable_price = 0.00;
        if (!empty($planId)) {
            $coursePlan = CoursePlan::where('id', $planId)->get()->first();
            if (!empty($coursePlan)) {
                // if($coursePlan->plan_type == CoursePlan::PLAN_RECURRING) {
                //     return $final_payable_price = $coursePlan->price;
                // } else {
                return $final_payable_price = $coursePlan->final_payable_price;
                // }
            }
        }
        return $final_payable_price;
    }
}

if (!function_exists('get_plan_data')) {
    function get_plan_data($planId = null)
    {
        $coursePlan = null;
        if (!empty($planId)) {
            $coursePlan = CoursePlan::where('id', $planId)->get()->first();
            if (!empty($coursePlan)) {
                return $coursePlan;
            }
        }
        return $coursePlan;
    }
}

if (!function_exists('course_purchased')) {
    function course_purchased($course_id = null)
    {
        if (empty($course_id) || !Auth::guard('learner')->check()) {
            return null;
        }

        return \App\Models\UserCourse::where('course_id', $course_id)->where('learner_id', Auth::guard('learner')->id())
        // ->where('order_status', true)
            ->exists();
    }
}

if (!function_exists('dateFormate')) {
    function dateFormate($createdAt)
    {
        // dump($createdAt);
        $start = Carbon::now();
        $end = Carbon::parse($createdAt);
        $days = $end->diffInDays($start);

        $result = $start->gte($end);

        if ($result == true) {
            return 1;
        }

        return $days + 2;

        // dump($days);
        // return !is_null($createdAt) ? date('d-m-Y', strtotime($createdAt)) : '';

    }
}

/**
 * @return Learner mobile number with Prefix Country Code
 */
if (!function_exists('learner_mobile_with_country_code')) {
    function learner_mobile_with_country_code()
    {
        if (auth()->guard('learner')->check()) {
            $learner = Learner::with('country:id,phonecode')->select('id', 'country_id', 'mobile')->where('id', auth()->guard('learner')->user()->id)->first();
            return isset($learner) && !empty($learner->mobile) && !empty($learner->country) && !empty($learner->country->phonecode) ? $learner->country->phonecode . $learner->mobile : '';
        }
        return '';
    }
}

function learner_mobile_with_country_code_id($user_id)
{
    if ($user_id) {
        $learner = Learner::with('country:id,phonecode')->select('id', 'country_id', 'mobile')->where('id', $user_id)->first();
        return isset($learner) && !empty($learner->mobile) && !empty($learner->country) && !empty($learner->country->phonecode) ? $learner->country->phonecode . $learner->mobile : '';
    }
    return '';
}

if (!function_exists('avg_course_rating')) {
    function avg_course_rating($course_id = 0)
    {
        return (float) (round(RatingReview::where('course_id', $course_id)->pluck('rating')->avg())) ?? 0;
    }
}

if (!function_exists('avg_inst_rating')) {
    function avg_inst_rating($inst_id = 0)
    {
        $courses = Course::where('instructor_id', $inst_id)->pluck('id');
        if (empty($courses)) {
            return 0;
        }
        return (float) (round(RatingReview::whereIn('course_id', $courses)->where('is_approve', true)->pluck('rating')->avg())) ?? 0;
    }
}
if (!function_exists('avg_inst_rating_count')) {
    function avg_inst_rating_count($inst_id = 0)
    {
        $courses = Course::where('instructor_id', $inst_id)->pluck('id');
        if (empty($courses)) {
            return 0;
        }
        return RatingReview::whereIn('course_id', $courses)->where('is_approve', true)->pluck('rating')->count();
    }
}

if (!function_exists('cpaginate')) {
    function cpaginate($value, $perPage = 10, $page = null, $options = [], $takevalues = false)
    {
        $totalGroup = count($value->all());
        if ($page == '') {
            $page = \Illuminate\Pagination\Paginator::resolveCurrentPage('page');
        }
        if ($takevalues) {
            $data = $value->forPage($page, $perPage);
        } else {
            $data = $value->forPage($page, $perPage)->values();
        }
        $data = new \Illuminate\Pagination\LengthAwarePaginator($data, $totalGroup, $perPage, $page, [
            'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
        return $data;
    }
}

if (!function_exists('get_course_detail_for_checkout')) {
    function get_course_detail_for_checkout($courseId = null, $planId = null)
    {
        if (!empty($courseId) && !is_null($courseId)) {
            $deviceType = Course::COURSE_WEBSITE;
            $course = Course::
                // whereIn('course_platform', [Course::COURSE_ALL, Course::COURSE_WEBSITE])
                whereRaw("find_in_set($deviceType , course_platform)")
                ->with(['instructor' => function ($query) {
                    $query->select('name', 'email', 'id', 'profile_picture');
                }, 'categories', 'wishlists', 'instructor.instructure', 'rating_reviews' => function ($query) {
                    return $query->where('is_approve', true);
                }])
                ->withCount([
                    'chapters' => function ($query) {
                        $query->where('parent_id', 0);
                    },
                    'rating_reviews as total_review',
                    'packages',
                ])
                ->withAvg('rating_reviews as AverageRating', 'rating')
            // ->leftJoin('course_plans as cp','cp.id','=','courses.default_web_price')
                ->whereRelation('categories', 'status', true)
                ->whereNull('courses.deleted_at')
            //->where('cp.status','1')
                ->where('courses.id', $courseId);

            if (!empty($planId) && !is_null($planId)) {
                $course->with(['plans' => function ($query) use ($planId, $courseId) {
                    $query->where('id', $planId); //->where('course_id', $courseId);
                }]);
            }
            return $course->first();
        }
        return collect();
    }
}

if (!function_exists('notification')) {
    function notification($title = '', $message = '', $ids = [], $type = 0, $course_id = 0, $manual_course_purchase_id = 0, $learner_manual_id = '', $scope = [], $manual_type = "", $redirect_type, $external_link, $manual_notify_id, $image_name)
    {

        // $notify_id = Notification::where("learnerId",$learner_manual_id)->where("courseId",$course_id)->orderBy("id","desc")->first()->id;
        // dd($notify_id);
        if ($type == 2) {
            $notify_type = 4;

            if (in_array('push', $scope) && in_array('web', $scope) && $manual_type == 'manual_notification') {
                $notify_type = 5;
            }
            $noti_id = Notification::create([
                'title' => $title,
                'text' => $message,
                'courseId' => $course_id,
                'learnerId' => $learner_manual_id,
                'target_link' => $redirect_type,
                'external_link' => $external_link,
                'type' => $notify_type,
                'manual_notify_id' => $manual_notify_id,
            ]);
        }
        $notified = false;
        $data = [];
        if (!empty($ids)) {

            $android_token = "fsjapojuTlC-WogEKn4BlP:APA91bE31f0nhoLRq-6j2eSn53P9vpC7R-GQzT4DPxbhk2a6UoDvV5O13XqM_g_WlvCIpfs6kAzU8wAqCLEqS3jp34QoE8U216qsrhPPDwWin3IksG-rwbuXtAG9CSP0B9OOPghfNfz5";
            // $android_token = "e38HeuWLR6a9c1EzsXCgn_:APA91bHIZi8qnoCuELXspXdvCeiJclYBR-YFjjPx6k6LQe4m5jzFAWFzz1e-5PWr5AeVPQu_tp2FHGlZIEm_lsxIqkCzKxuYebExUPBrjU1jbfQQ8c3egoSQW0ilN-pkxMSM887cdlOj";
            // working token device not available
            // $android_token = "ekcZp1AWRi-NlLIZ0cXpeV:APA91bFDKMPIQMEw0PVvRkyLCNIHJXGLlHbNb3y9hZG0-5y8ENxwsVznOzri92fjOQYLW7yGpwrv7UTqOUjibqM5a6ao2CReTG341A4kj-pymM-Vzc1YA8EJEtS-uuNIba5z-DsmXr3Q";
            // $ios_token = "ek3CkEeCIE4_kK51DsHE4H:APA91bFNQL0a8kufDz40TemQXdI8OfrapUEhZiGUCA03esyZaSZXr9o-c9qneefLMazy3bg66sXsS2VdiQfI4dSt0P2E09jw9OByxCXVS5T3SiuQvMrJqO4ODz4VKtwt5eiPr8QmbIZt";
            $ios_token = "c7FCT0NX10X0pHWh49tdfC:APA91bGdSE4uiOUx12RLduCr7HvUhEY76YP-EyzNWj32VPekBIzcnWq54q28Y_BVQODk-5LNjmf0Dt6zhLP0z_AvXq6fpWiPjGkRECpSLnfO8owc7Mqqk-SYDcsS8wUEAL1PAJS0NyzG";
            // $ios_token = "devicetoken : f7-njYtjGk-hqA8ZPR2rIr:APA91bG4IVLkt4leH-feT-VBh-aPZ_fsrHUeLBF8B9d6tqE_uvVDk-OhE7F-uy-m3eqOvVq9VxINZc0i8DCC917d3XOgInKLI5yJv5e4dOva5FS0C_EJzsQJq1ym-5ofUBHxiu7INbxk";
            // $ios_token = "dDWSVYyDMEoSvFaIVtDePK:APA91bF6EsphPEESgL-lSUbJEP4CxBO3CE-lEDDCscYyWWYoTacdcDGxz9m3m9O1eXgZJBHN85TnRTH8yvJMVWre0AXTXzG_8SpbfMQAswNmEvNvy5TGxg3ZG5cRebQneeEpBWlPvLVn";
            // $ios_token = "e46K2J7hsE71u_po9I6jSJ:APA91bFgBFJE0VT0ORgHXM4pk6HP8iWo-YbdaK-3HyzV3IkpOpciEVqw3foRA7Y7px4KZnycPYcs3H4AMYcojvRehwZ70DqqbOCa5lJssa-AkKLnhCZJ6y-xhlRayasbySJkyuPakMib";
            // $fcmUrl = "https://fcm.googleapis.com/fcm/send";

            // $learner_id = (array)(Auth::guard('learner')->id());
            $token = DeviceToken::select('device_token')->whereIn('learner_id', $ids)->orderBy('id', 'DESC')->pluck('device_token')->toArray();
            // $token = DeviceToken::select('device_token')->whereIn('learner_id',$ids)->orderBy('id', 'desc')->first()->device_token;
            // $token = DeviceToken::select('device_token')->whereIn('learner_id', [688])->orderBy('id', 'DESC')->pluck('device_token')->toArray();
            // dd($token);
            $token = array_unique($token);
            $token = array_filter($token);
            // $token[] = $android_token; //android
            // $token[] = $ios_token; //ios

            if (!empty($token) && !empty($title) && !empty($message)) {

                $notification = [
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'course_id' => $course_id,
                    'manual_course_purchase_id' => $manual_course_purchase_id,
                    'learner_manual_id' => $learner_manual_id,
                    'external_link' => $external_link,
                    'image_name' => $image_name,

                ];

                $fcmNotification = [
                    'to' => $token,
                    'notification' => $notification,
                ];

                foreach ($token as $tkn) {
                    $notified = notifiy($fcmNotification, $tkn, $ids);
                }

                // $data['android']=notifiy($fcmUrl,$fcmNotification,$androidkey);
                // $data['ios']=$this->notifiy($fcmUrl,$fcmNotification,$ioskey);
            }
        }

        return $notified;
    }
}

if (!function_exists('getGoogleAccessToken')) {
    function getGoogleAccessToken()
    {

        $credentialsFilePath = storage_path('keys/life-gurukul-v2-firebase-adminsdk-y7ck7-05444ec86a.json');
        $client = new \Google_Client();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();
        return $token['access_token'];
    }
}

if (!function_exists('notifiy')) {
    function notifiy($fcmNotification, $token, $learner_id)
    {
        // dd($fcmNotification['notification']['learner_manual_id']);
        $resp = false;
        $title = $fcmNotification['notification']['title'];
        $learner_manual_id = $fcmNotification['notification']['learner_manual_id'];
        $message = $fcmNotification['notification']['message'];
        $type = isset($fcmNotification['notification']['type']) ? $fcmNotification['notification']['type'] : 0;
        $external_link = isset($fcmNotification['notification']['external_link']) ? $fcmNotification['notification']['external_link'] : 0;
        $course_id = isset($fcmNotification['notification']['course_id']) ? $fcmNotification['notification']['course_id'] : 0;
        $manual_course_purchase_id = isset($fcmNotification['notification']['manual_course_purchase_id']) ? $fcmNotification['notification']['manual_course_purchase_id'] : 0;
        $image_name = isset($fcmNotification['notification']['image_name']) ? $fcmNotification['notification']['image_name'] : 0;

        $l_id = isset($learner_manual_id) ? $learner_manual_id : @auth()->user()->id;

        $notify_id = @Notification::where("learnerId", $l_id)
            ->where("courseId", $course_id)->orderBy("id", "desc")->first()->id;

        // dd($notify_id);
        if ($course_id > 0) {
            $type_data = Course::select('type')->where('id', $course_id)->first()->toArray();

            $isCombineCourse = isset($type_data['type']) && $type_data['type'] == '1' ? '0' : '1';
        }

        $course_categories = Course::select('id')->with(['userCourse' => function ($query) use ($course_id, $learner_id) {
            $query->where('course_id', $course_id)->where('learner_id', $learner_id);
        }])->where('id', $course_id)->get()->toArray();

        $course_purchase_id = 0;
        if (isset($course_categories[0]['user_course']['id']) && !empty($course_categories[0]['user_course']['id'])) {
            // dd($course_categories[0]['user_course']['id']);
            $course_purchase_id = $course_categories[0]['user_course']['id'];
        }
        $error_msg = '';
        $projectID = 'life-gurukul-v2';
        // please use common function
        //$SERVER_API_KEY = config()->has('settings.fcm_server_api_key') ? config('settings.fcm_server_api_key') : null;
        // foreach ($token as $key => $value) {

        if (!empty($token) /*&& !empty($SERVER_API_KEY)*/) {
            if ($type == 2) {
                $course_purchase_id = $manual_course_purchase_id;
            }

            // $notify_data = [

            // "sound" => "default",
            // "title" => (string) $title,
            // "body" => (string) $message,
            // "type" => (string) $type,
            // "course_id" => (string) $course_id,
            // 'course_purchase_id' => (string) $course_purchase_id,
            // 'notify_id' => (string)$notify_id,
            //     // "aps" => [/*"alert" => "Test Push Message", "sound" => "default"]
            // ];
            // dd($learner_manual_id);

            $notify_data = [
                'message' => [
                    // "token" => 'f4Wg4uqETnWSYJv5WUCXBm:APA91bFtiTPSiV2i2lTP5pIZnKzyYyad0PHbaGPpG4NVDrHtrwzM_EwBX7IjHKsdCJg_PmFdzGvwQnBT0qBLCn_FkUN4en4Kai1fnA2ep-llKJIIlSDoMKdE2gJyTrj9Hgd1oUWJ9V1g',
                    "token" => $token,
                    "notification" => [
                        "title" => (string) $title,
                        "body" => (string) $message,
                    ],
                    'data' => [
                        "title" => (string) $title,
                        "body" => (string) $message,
                        "type" => (string) $type,
                        "course_id" => (string) $course_id,
                        "user_id" => (string) $learner_manual_id,
                        'course_purchase_id' => (string) $course_purchase_id,
                        'notify_id' => (string) $notify_id,
                        'external_link' => (string) $external_link,
                        'image_url' => (string) url($image_name),
                        'mutable-content' => (string) 1,
                        'mutable-content' => (string) 1,
                    ],
                    "apns" => [
                        "payload" => [
                            "aps" => [
                                "mutable-content" => 1,
                            ],
                        ],
                        "fcm_options" => [
                            'image' => (string) url($image_name),
                        ],
                    ],
                    "android" => [
                        "notification" => [
                            'image' => (string) url($image_name),
                        ],
                    ],
                    "webpush" => [
                        "headers" => [
                            "image" => "https://foo.bar/pizza-monster.png",
                        ],
                    ],
                ],
            ];
            // $data = [
            // "registration_ids" => [$value],
            // "registration_ids" => array_values($token),
            // "registration_ids" => "fviubbaZRcWFVE0DwpdLjt:APA91bE3Wwuxk4qBtwV6ou0GZJCOzaOvHKpdGThKqXGIJFZoRiENTtthjBNrtZCAIde9Fowh_diK5FC8u_mFFx85I1wj_5BOCo-So4EmVJ8VOE_gxh08-VYB76qYqk_4Wh4kYbh7GbFH",
            // android
            // "registration_ids" => ['ekcZp1AWRi-NlLIZ0cXpeV:APA91bFDKMPIQMEw0PVvRkyLCNIHJXGLlHbNb3y9hZG0-5y8ENxwsVznOzri92fjOQYLW7yGpwrv7UTqOUjibqM5a6ao2CReTG341A4kj-pymM-Vzc1YA8EJEtS-uuNIba5z-DsmXr3Q'],

            // ios
            // "registration_ids" => ['e46K2J7hsE71u_po9I6jSJ:APA91bFgBFJE0VT0ORgHXM4pk6HP8iWo-YbdaK-3HyzV3IkpOpciEVqw3foRA7Y7px4KZnycPYcs3H4AMYcojvRehwZ70DqqbOCa5lJssa-AkKLnhCZJ6y-xhlRayasbySJkyuPakMib'],

            // "notification" => $notify_data,
            // "datas" => $notify_data,
            // ];

            if ($course_id > 0) {

                $notify_data['message']["data"]['isCombineCourse'] = (string) $isCombineCourse;
                // $data["data"]['isCombineCourse'] = (string) $isCombineCourse;
            }
            // $data = [

            //     "notification" => $notify_data,
            //     "data" => $notify_data,
            // ];

            $dataString = json_encode($notify_data);

            // dd($dataString);

            // $headers = [
            //     'Authorization: key=' . $SERVER_API_KEY,
            //     'Content-Type: application/json',
            // ];
            $apiurl = 'https://fcm.googleapis.com/v1/projects/' . $projectID . '/messages:send';
            $headers = [
                'Authorization: Bearer ' . getGoogleAccessToken(),
                'Content-Type: application/json',
            ];
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $apiurl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
            }
            $res = json_decode($response);

            // if ($res->success == 1) {
            // dump($res, $error_msg);
            // if ($res->success == 1) {
            //     $resp = true;
            // }
            if ($res != '') {
                $resp = true;
            }
        }
        // }
        return $resp;
    }
}

if (!function_exists('gstCal')) {
    function gstCal($data, $gst = null)
    {

        $total_gst = 0.00;
        $igst = config()->has('settings.igst') ? config('settings.igst') : null;
        $sgst = config()->has('settings.sgst') ? config('settings.sgst') : null;
        $cgst = config()->has('settings.cgst') ? config('settings.cgst') : null;
        $price = ($data->price - ($data->user_coin * $data->per_coin_price) - $data->after_coupon_applied_deduction_price);

        if (auth()->guard("learner")->check() == false) {
            $total_price = $price * 100 / 118;
            $gst = $total_price * $gst / 100;
        } else {
            if ($data->country_id == 1) {
                if ($data->state_id == 12) {
                    $total_price = $price * 100 / 118;
                    $gst = $total_price * $gst / 100;
                } else {
                    $total_price = $price * 100 / 118;
                    $gst = $total_price * $gst / 100;
                }
            } else {
                $total_price = $price;
            }
        }

        return [number_format($total_price, 2, '.', ''), number_format($gst, 2, '.', '')];
    }
}

if (!function_exists('youtube_api_key')) {
    function youtube_api_key()
    {
        // return env('YOUTUBE_API_KEY') ?? false;
        return config()->has('settings.youtube_client_secret') ?? false;
    }
}
if (!function_exists('vimeo_access_token')) {
    function vimeo_access_token()
    {
        return env('VIMEO_ACCESS_TOKEN') ?? false;
    }
}
// if(!function_exists('vimeo_access_token')){
//     function vimeo_access_token()
//     {
//         return env('VIMEO_ACCESS_TOKEN') ?? false;
//     }
// }

if (!function_exists('get_plan_expire')) {
    function get_plan_expire($planId = null)
    {

        $expiredAt = null;
        if (!empty($planId) && !is_null($planId)) {
            $plans = CoursePlan::
            // where('status', 1)
                where('id', $planId)
                ->where('course_limit', 1)
                ->get()->first();
            $date = new DateTime();
            // dd($date);
            if (isset($plans) && !empty($plans)) {
                if (isset($plans->is_fixed_date)) {
                    if ($plans->is_fixed_date == 2) { // add number of days
                        if (!empty($plans->access_value)) {
                            $date->modify('+' . ($plans->access_value - 1) . ' days');
                        }
                        // $date->modify('+20 days');
                        $expiredAt = $date->format('Y-m-d');

                        // $date = strtotime("+10 day", strtotime(date('Y-m-d')));
                        // $expiredAt = date('Y-m-d', $date);

                    }
                    if ($plans->is_fixed_date == 1) { // add expiredate
                        $expiredAt = $plans->access_value;
                    }
                    return $expiredAt;
                }
            }
        }
        return $expiredAt;
    }
}
/*******
 * $validTill = 1 // expiredAt
 * $validTill = 2 //Lifetime
 * $validTill = 0 //Expired
 * ********/
if (!function_exists('is_expired')) {
    function is_expired($expiredAt = null)
    {
        if (!empty($expiredAt) && !is_null($expiredAt)) {
            $date = new DateTime();
            $currentDate = $date->format('Y-m-d');
            if (strtotime($currentDate) <= strtotime($expiredAt)) {
                $validTill = 1; //$expiredAt;
            } else {
                $validTill = 0; //" Expired";
            }
        } else {

            $validTill = 2; //" Lifetime";
        }
        return $validTill;
    }
}
if (!function_exists('get_course_validity')) {
    function get_course_validity($expiredFlag = null, $expiredAt = null)
    {
        if ($expiredFlag == 1) {
            $valid_till = $expiredAt;
        } else if ($expiredFlag == 2) {
            $valid_till = 'Lifetime';
        } else {
            $valid_till = 'Expired';
        }
        return $valid_till;
    }
}

if (!function_exists('send_notification')) {
    function send_notification($params, $type, $scope, $subject, $content, $file_name = "")
    {
        if (empty($params) || empty($type) || empty($scope) || empty($subject) || empty($content)) {
            return false;
        }

        return new NotificationController($params, $type, $scope, $subject, $content, $file_name);
    }
}

if (!function_exists('getBillFrequency')) {
    function getBillFrequency($id = null)
    {
        //    1-weekly, 2-monthly, 3-yearly //daily
        if ($id == 1) {
            return "weekly";
        } elseif ($id == 2) {
            return "monthly";
        } elseif ($id == 3) {
            return "yearly";
        }
    }
}

if (!function_exists('add_rating_review')) {
    function add_rating_review($data = [])
    {
        if (empty($data)) {
            return false;
        }
        return RatingReview::create($data);
    }
}

if (!function_exists('list_rating_review')) {
    function list_rating_review($deleted = true)
    {
        if ($deleted == true) {
            return RatingReview::withTrashed()->get();
        } else {
            return RatingReview::all();
        }
    }
}

if (!function_exists('edit_rating_review')) {
    function edit_rating_review($data = [], $id = null)
    {
        if (count(array_filter(array_values($data))) == 0 | empty($id) | $id <= 0) {
            return false;
        }
        return RatingReview::where('id', $id)->update($data);
    }
}

if (!function_exists('delete_rating_review')) {
    function delete_rating_review($id = null)
    {
        if (empty($id) | $id <= 0) {
            return false;
        }
        return RatingReview::where('id', $id)->delete();
    }
}

if (!function_exists('get_invoice_data')) {
    function get_invoice_data($id = null)
    {
        if (empty($id) | $id <= 0) {
            return false;
        }
        // dd($id);
        $invoiceData = UserCourse::with(['course', 'learner', 'learner.country', 'learner.state', 'learner.city', 'coursePlan:id,course_id,plan_type,final_payable_price'])
            ->where('id', $id)
            ->latest()
            ->get()->first();

        return $invoiceData;
    }
}
if (!function_exists('get_invoice_dataV2')) {
    function get_invoice_dataV2($id = null)
    {
        if (empty($id) | $id <= 0) {
            return false;
        }
        // dd($id);
        $invoiceData = UserCourse::with(['course', 'learner', 'learner.country', 'learner.state', 'learner.city', 'coursePlan:id,course_id,plan_type,final_payable_price'])
            ->where('course_id', $id)
            ->latest()
            ->get()->first();
        // dd   ($invoiceData);
        return $invoiceData;
    }
}
if (!function_exists('get_invoice_dataV3')) {
    function get_invoice_dataV3($id = null)
    {
        if (empty($id) | $id <= 0) {
            return false;
        }
        // dd($id);
        $invoiceData = UserCourse::with(['course', 'learner', 'learner.country', 'learner.state', 'learner.city', 'coursePlan:id,course_id,plan_type,final_payable_price'])
            ->where('id', $id)
            ->latest()
            ->get()->first();
        // dd   ($invoiceData);
        return $invoiceData;
    }
}

if (!function_exists('get_learner_data')) {
    function get_learner_data($id = null)
    {
        if (empty($id) | $id <= 0) {
            return false;
        }
        $learnerData = Learner::where('id', $id)
            ->latest()
            ->get()->first();
        return $learnerData;
    }
}

if (!function_exists('getDeviceType')) {
    function getDeviceType($id = null)
    {
        //    1-android, 2-Ios, 3-WEB
        if ($id == 1) {
            return "Android";
        } elseif ($id == 2) {
            return "IOS";
        } elseif ($id == 3) {
            return "Web";
        }
    }
}

if (!function_exists('min_max_price_btn')) {
    /*******
     * $pageType = 1 // homepage
     * $pageType = 2 //details Page of course and package page
     *******/
    function min_max_price_btn($priceData = [], $pageType = 0)
    {
        $min = 0;
        $max = 0;
        $free = 0;
        if (empty($priceData)) {
            return false;
        }

        try {
            if (!empty($priceData)) {
                if ($priceData['plan_type'] == 0) { // free
                    $free = 1;
                }
                if ($priceData['plan_type'] == 2) { // recuring
                    $max = $priceData['final_payable_price'];
                    $free = 2;
                }
                if ($priceData['plan_type'] == 1) { // one time
                    $max = $priceData['final_payable_price'];
                    $min = $priceData['list_price'];
                    $free = 0;
                }
                $button = '';
                if ($pageType == 1) { // HomePage
                    if ($free == 0) {
                        $button = '<div class="price"><h3>₹' . $max . ' <span>₹' . $min . '</span></h3></div>';
                    } elseif ($free == 2) {
                        $button = '<div class="price"><h3>₹' . $max . '</h3></div>';
                    } elseif ($free == 1) {
                        $button = '<div class="price"><h3> FREE </h3></div>';
                    }
                }
                if ($pageType == 2) { // HomePage
                    if ($free == 0) {
                        $button = '<div class="course-fee"><h2><span style="text-decoration:none;">₹' . $max . ' </span></h2><p><span>₹' . $min . '</span></p></div>';
                    } elseif ($free == 2) {
                        $button = '<div class="course-fee"><h2><span style="text-decoration:none;">₹' . $max . '</span></h2></div>';
                    } elseif ($free == 1) {
                        $button = '<div class="course-fee"><h2><span style="text-decoration:none;">FREE</span></h2></div>';
                    }
                }
                echo $button;
            }
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}

/**
 * @return array like ['slug' => 'url'];
 */
if (!function_exists('find_cms_pages')) {
    function find_cms_pages()
    {
        $slugArray = [
            'about-us', 'refund-policy', 'privacy-policy', 'term-condition',
        ];

        return Page::whereIn('slug', $slugArray)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->get(['slug'])
            ->transform(function ($item) {
                $item = [$item->slug => route('page', ['slug' => $item->slug])];
                return $item;
            })->collapse()->toArray();
    }
}

/**
 * Check 'slug' is exists or not in given Pages array.
 * @return true || false.
 */
if (!function_exists('is_menu_enable')) {
    function is_menu_enable($slug, $pagesKeyValueArray = [])
    {
        if (!is_null($slug) && !empty($pagesKeyValueArray)) {
            if (array_key_exists($slug, $pagesKeyValueArray)) {
                return true;
            }
        }
        return false;
    }
}

/**
 * Check 'slug' is exists or not in given array.
 * @return Page URL || 'javascript:void(0)'
 */
if (!function_exists('cms_menu_url')) {
    function cms_menu_url($key, $pagesKeyValueArray = [])
    {
        if (!empty($key) && !empty($pagesKeyValueArray)) {
            if (array_key_exists($key, $pagesKeyValueArray)) {
                return $pagesKeyValueArray[$key];
            }
        }
        return 'javascript:void(0)';
    }
}

if (!function_exists('getNotificationData')) {
    //get all notification based on learner
    function getNotificationData()
    {
        $notifications = [];
        if (Auth::guard('learner')->check()) {
            $learner_id = Auth::guard('learner')->id();
            //where('learner_id',$learner_id)->
            $notifications = Notification::with('course')->where('learnerId', $learner_id)->orderBy('id', 'DESC')->take(5)->get();
            return $notifications;
        }
    }
}

if (!function_exists('timeAgo')) {
    // times get from date time stamp
    function timeAgo($time_ago)
    {
        $time_ago = strtotime($time_ago);
        $cur_time = time();
        $time_elapsed = $cur_time - $time_ago;
        $seconds = $time_elapsed;
        $minutes = round($time_elapsed / 60);
        $hours = round($time_elapsed / 3600);
        $days = round($time_elapsed / 86400);
        $weeks = round($time_elapsed / 604800);
        $months = round($time_elapsed / 2600640);
        $years = round($time_elapsed / 31207680);
        // Seconds
        if ($seconds <= 60) {
            return "Just now";
        }
        //Minutes
        else if ($minutes <= 60) {
            if ($minutes == 1) {
                return "1 minute ago";
            } else {
                return "$minutes minutes ago";
            }
        }
        //Hours
        else if ($hours <= 24) {
            if ($hours == 1) {
                return "An hour ago";
            } else {
                return "$hours hrs ago";
            }
        }
        //Days
        else if ($days <= 7) {
            if ($days == 1) {
                return "Yesterday";
            } else {
                return "$days days ago";
            }
        }
        //Weeks
        else if ($weeks <= 4.3) {
            if ($weeks == 1) {
                return "A week ago";
            } else {
                return "$weeks weeks ago";
            }
        }
        //Months
        else if ($months <= 12) {
            if ($months == 1) {
                return "1 month ago";
            } else {
                return "$months months ago";
            }
        }
        //Years
        else {
            if ($years == 1) {
                return "One year ago";
            } else {
                return "$years years ago";
            }
        }
    }
}
if (!function_exists('connection')) {
    function connection($title, $admin_name, $folder_status = null)
    {
        $api_key = config()->has('settings.vdocipher_key') ? config('settings.vdocipher_key') : null;
        if (isset($api_key) && $api_key != null) {

            if ($folder_status == 1) {
                $folder = config()->has('settings.vdocipherintroductionvideo') ? config('settings.vdocipherintroductionvideo') : "43255728ebb94a45a591713a83310852";
            } else {

                $folder = config()->has('settings.vdocipher_folder') ? config('settings.vdocipher_folder') : "cead6ea9e2974701bf5fc7a2257c2d60";
            }

            $curl = curl_init();

            curl_setopt_array($curl, array(
                // CURLOPT_URL => "https://dev.vdocipher.com/api/videos?title=title&folderId=" . $folder,
                CURLOPT_URL => 'https://dev.vdocipher.com/api/videos?title=' . $admin_name . '_' . $title . '&folderId=' . $folder,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "PUT",
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Apisecret $api_key",
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                return $response;
            }
        }
    }
}

if (!function_exists('getVideo')) {
    function getVideo($videoId)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://dev.vdocipher.com/api/videos/$videoId/files",
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

        $responses = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        return json_decode($responses);
    }
}
if (!function_exists('getVideoUrl')) {
    function getVideoUrl($is_id, $videoId)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://dev.vdocipher.com/api/videos/$videoId/files/$is_id",
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

        $responses = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        return json_decode($responses);
    }
}

if (!function_exists('upload_vdo_cipher')) {
    function upload_vdo_cipher($mediaId, $file, $file_new = null, $title = null, $user_name = null)
    {
        // if ($file->getPathName() && $mediaId > 0) {
        // print_r($file->getClientOriginalName());

        if ($mediaId > 0) {
            // dd($mediaId );

            $filePath = "";

            if ($file_new) {
                $title_name = str_replace(' ', '_', $title);

                $response = connection($title_name, $user_name);
                $filePath = $file_new ?? null;
            } else {
                $title_name = str_replace(' ', '_', $file->getClientOriginalName());
                $user_name_split = @str_replace(' ', '_', @auth()->user()->name);
                $response = connection($title_name, $user_name_split);
                $filePath = $file->getPathName() ?? null;
            }

            // below response was obtained in Step 1
            $responseObj = json_decode($response);

            // save this id in your database with status 'upload-pending'
            // var_dump($responseObj->videoId);
            if ($mediaId > 0 && isset($responseObj->videoId)) {
                $media = Media::where('id', $mediaId)->first();
                $media->update([
                    'videoId' => $responseObj->videoId,
                ]);
            }
            if (isset($responseObj->clientPayload)) {
                $uploadCredentials = $responseObj->clientPayload;
                $ch = curl_init($uploadCredentials->uploadLink);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, [
                    'policy' => $uploadCredentials->policy,
                    'key' => $uploadCredentials->key,
                    'x-amz-signature' => $uploadCredentials->{'x-amz-signature'},
                    'x-amz-algorithm' => $uploadCredentials->{'x-amz-algorithm'},
                    'x-amz-date' => $uploadCredentials->{'x-amz-date'},
                    'x-amz-credential' => $uploadCredentials->{'x-amz-credential'},
                    'success_action_status' => 201,
                    'success_action_redirect' => '',
                    'file' => new \CurlFile($filePath, 'image/png', 'filename.png'),
                ]);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

                // get response from the server
                $response = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $err = curl_error($ch);

                curl_close($ch);

                if (!$err && $httpcode === 201) {
                    return 1;
                    // echo "upload successful";
                } else {
                    return $err;
                    // echo "upload failed due to " . (($err) ? $err : $response);
                }
            } else {
                return 0;
            }
        }
    }
}

if (!function_exists('intro_upload_vdo_cipher')) {
    function intro_upload_vdo_cipher($course_id, $file)
    {
        if ($course_id > 0) {

            $title_name = str_replace(' ', '_', $file->getClientOriginalName());
            $response = connection($title_name, $user_name = auth()->user()->name, 1);
            $filePath = $file->getPathName() ?? null;

            // below response was obtained in Step 1
            $responseObj = json_decode($response);

            // dd($responseObj);

            // save this id in your database with status 'upload-pending'
            // var_dump($responseObj->videoId);
            if ($course_id > 0 && isset($responseObj->videoId)) {
                $course = Course::where('id', $course_id)->first();
                $course->update([
                    'videoId' => $responseObj->videoId,
                ]);
            }
            if (isset($responseObj->clientPayload)) {
                $uploadCredentials = $responseObj->clientPayload;
                $ch = curl_init($uploadCredentials->uploadLink);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, [
                    'policy' => $uploadCredentials->policy,
                    'key' => $uploadCredentials->key,
                    'x-amz-signature' => $uploadCredentials->{'x-amz-signature'},
                    'x-amz-algorithm' => $uploadCredentials->{'x-amz-algorithm'},
                    'x-amz-date' => $uploadCredentials->{'x-amz-date'},
                    'x-amz-credential' => $uploadCredentials->{'x-amz-credential'},
                    'success_action_status' => 201,
                    'success_action_redirect' => '',
                    'file' => new \CurlFile($filePath, 'image/png', 'filename.png'),
                ]);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

                // get response from the server
                $response = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $err = curl_error($ch);

                curl_close($ch);

                if (!$err && $httpcode === 201) {
                    return 1;
                    // echo "upload successful";
                } else {
                    return 0;
                    // echo "upload failed due to " . (($err) ? $err : $response);
                }
            } else {
                return 0;
            }
        }
    }
}

if (!function_exists('getVideoTokenData')) {
    function getVideoTokenData($videoId)
    {

        $curl = curl_init();
        $api_key = config()->has('settings.vdocipher_key') ? config('settings.vdocipher_key') : null;
        if (isset($api_key) && $api_key != null) {
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://dev.vdocipher.com/api/videos/$videoId/otp",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode([ // default 6 hours
                    // "ttl" => 300,
                    "userId" => @auth()->guard("learner")->user()->id,
                ]),
                CURLOPT_HTTPHEADER => array(
                    "Accept: application/json",
                    "Authorization: Apisecret $api_key",
                    "Content-Type: application/json",
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                // echo "cURL Error #:" . $err;
                return 0;
            } else {
                $responseObj = json_decode($response);

                // dd($responseObj);
                return $responseObj;
            }
        }
    }
}
if (!function_exists('getVideoOfflineTokenData')) {
    function getVideoOfflineTokenData($videoId, $days)
    {
        $days = ($days == '') ? 365 : $days;

        $curl = curl_init();
        $api_key = config()->has('settings.vdocipher_key') ? config('settings.vdocipher_key') : null;
        if (isset($api_key) && $api_key != null) {
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://dev.vdocipher.com/api/videos/$videoId/otp",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode([
                    'licenseRules' => json_encode([
                        'canPersist' => true,
                        'rentalDuration' => $days * 24 * 3600, // 15 days (make it dynamic like based on user expiration date)
                    ]),
                    "userId" => @auth()->guard("learner")->user()->id,
                ]),

                CURLOPT_HTTPHEADER => array(
                    "Accept: application/json",
                    "Authorization: Apisecret $api_key",
                    "Content-Type: application/json",
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                // echo "cURL Error #:" . $err;
                return 0;
            } else {
                $responseObj = json_decode($response);
                return $responseObj;
            }
        }
    }
}
// NOTE: It is not in USE for Now
/*********
 * remove video from Vdo Cipher
 * videoIds array from media table
 * *********/
if (!function_exists('deleteVideo')) {
    function deleteVideo($videoIds = [])
    {
        $api_key = config()->has('settings.vdocipher_key') ? config('settings.vdocipher_key') : null;
        if (!empty($videoIds)) {
            $videoid = implode(",", $videoIds);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://dev.vdocipher.com/api/videos?videos=$videoid",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "DELETE",
                CURLOPT_HTTPHEADER => array(
                    "Accept: application/json",
                    "Authorization: Apisecret $api_key",
                    "Content-Type: application/json",
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return 0;
            } else {
                $responseObj = json_decode($response);
                return $responseObj;
            }
        }
    }
}

if (!function_exists('getVideoPoster')) {
    function getVideoPoster($videoId, $file)
    {
        $api_key = config()->has('settings.vdocipher_key') ? config('settings.vdocipher_key') : null;
        if ($file->getPathName() && $videoId > 0) {
            $filePath = $file->getPathName() ?? null;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://dev.vdocipher.com/api/videos/$videoId/files",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_POSTFIELDS => [
                    'file' => curl_file_create($filePath, $_FILES['file']['type']),
                ],
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: multipart/form-data",
                    "Accept: application/json",
                    "Authorization: Apisecret $api_key",
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if ($err) {
                return 0;
                //return "cURL Error #:" . $httpCode . " - " . $err;
            } else {
                $responseObj = json_decode($response);
                return $responseObj;
            }
        }
    }
}
if (!function_exists('getActivePayment')) {
    function getActivePayment()
    {
        $razorpay = config()->has('settings.razorpay_status') ? config('settings.razorpay_status') : null;
        if ($razorpay == 1) {
            return "razorpay";
        }
        $instamojo = config()->has('settings.instamojo_status') ? config('settings.instamojo_status') : null;
        if ($instamojo == 1) {
            return "instamojo";
        }
    }
}

/**
 *
 * Create Invoice
 */
if (!function_exists('createInvoice')) {
    function createInvoice($userCourseId = null)
    {
        if (is_null($userCourseId) && empty($userCourseId)) {
            return false;
        }

        ini_set('max_execution_time', 180);
        // Storage::disk('local')->makeDirectory('/invoice',0777);
        // chmod(storage_path('app/invoice'), 0777);
        $directoryPath = storage_path('app/public/invoice');
        if (!File::exists($directoryPath)) {
            File::makeDirectory($directoryPath, 0777, true); // Create with 0777 permissions
        }

        $invoiceData = get_invoice_data($userCourseId);
        // dd($invoiceData);
        // pdf download from here

        $pdf = MPDF::loadView('front.student.view_invoice_device', compact('invoiceData'));

        // $path = storage_path('app/public/invoice'); //  public_path('invoice/');
        $fileName = 'invoice_' . $userCourseId . '.pdf';
        $pdf->save($directoryPath . '/' . $fileName);
        UserCourse::where('id', $userCourseId)->update(['invoice' => "invoice/" . $fileName]);
    }
}

/**
 * Video Type
 * @return string || null
 */
if (!function_exists('checkVideoType')) {
    function checkVideoType($course)
    {
        if (is_null($course) || empty($course)) {
            return '';
        }

        $videoType = '';
        if (isset($course->asset_type) && $course->asset_type == 0) { // for video
            if (!is_null($course->upload_type)) {
                if ($course->upload_type == 0 && isset($course->media) && !empty($course->media) && !empty($course->media->videoId)) {
                    $videoType = 'VdoCipher';
                } elseif ($course->upload_type == 1) {
                    $videoType = 'Youtube';
                } elseif ($course->upload_type == 2) {
                    $videoType = 'Vimeo';
                }
            }
        } else if (isset($course->asset_type) && $course->asset_type == 1) { // for audio
            $videoType = 'VdoCipher';
        }
        return $videoType;
    }
}

/**
 * To check specific course is expire or not
 * @return Boolean (true || false)
 */
if (!function_exists('isUserCourseExpired')) {
    function isUserCourseExpired($expireAt = null)
    {
        // dump($expireAt);
        if (is_null($expireAt) || empty($expireAt)) {
            return true; // null or empty means 'LifeTime'
        }
        $date = new DateTime();
        $currentDate = $date->format('Y-m-d');
        if (strtotime($expireAt) >= strtotime($currentDate)) {
            /**
             * $expireAt = 2023-03-26
             * $currentDate = 2023-03-20
             * if 26 >= 20 then return true 'Means plan is currently active not expired.'
             */

            return true;
        }
        // dump($expireAt);
        return false;
    }
}
/**
 * To check specific course is expire or not
 * @return Boolean (true || false)
 */
if (!function_exists('sendSms')) {
    function sendSms($destinationNumber = '', $otp = '')
    {

        $auth_id = config()->has('settings.plivo_auth_id') ? config('settings.plivo_auth_id') : null;
        $auth_token = config()->has('settings.plivo_auth_token') ? config('settings.plivo_auth_token') : null;
        if (!empty($auth_id) && !empty($auth_token)) {

            # SMS sender ID.
            //    $src = config()->has('settings.plivo_send_mobile') ? config('settings.plivo_send_mobile') : NULL;
            $src = 'LFGURU';
            // $src = '1705167990063484077';
            //'+918866003372';

            # SMS destination number
            $dst = $destinationNumber;

            # SMS text {#var#}
            // $text = $otp . " is your One time Password to login to your Life Gurukul App Account. The OTP is valid for 10 minutes. - Life Gurukul Initiative by Sneh Desai";
            // $text = "Your OTP for Life Gurukul login is '".$otp."'. The OTP is valid for 10 minutes. Life Gurukul Initiative by Sneh Desai";
            // $text = $otp. " is your One time Password to login to your Life Gurukul App Account. The OTP is valid for 10 minutes. -Life Gurukul Initiative by Sneh Desai";
            //    $text = 'Lifegurukul login otp is '.$otp;

            $url = 'https://api.plivo.com/v1/Account/' . $auth_id . '/Message/';
            // old
            //    $data = array("src" => $src, "dst" => "$dst", "text" => "$text", "dlt_entity_id" => "1701158030428812617", "dlt_template_id" => "1707168976053083497", "dlt_template_category" => "transactional");

            // new
            $data = array("src" => $src, "dst" => "$dst", "text" => "$otp is your One time Password to login to your Life Gurukul App Account. The OTP is valid for 10 minutes. -Life Gurukul Initiative by Sneh Desai", "dlt_entity_id" => "1701158030428812617", "dlt_template_id" => "1707169104369826714", "dlt_template_category" => "service_implicit");

            $data_string = json_encode($data);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
            curl_setopt($ch, CURLOPT_USERPWD, $auth_id . ":" . $auth_token);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json'));
            $response = curl_exec($ch);
            $err = curl_error($ch);
            // dd( $response);
            curl_close($ch);
            //    if ($err) {
            //         return false;
            //     //    echo "cURL Error #:" . $err;
            //    } else {
            //         return true;
            //         // $response= json_decode($response);
            //    }
            return true;
        }
    }
}

/**
 * To check specific course is expire or not
 * @return Boolean (true || false)
 */
if (!function_exists('sendWAsms')) {
    function sendWAsms($destinationNumber = '', $otp = '',$name="")
    {

        $data = [
            "apiKey" => config('keyConfig.api_key'),
            "campaignName" => "LifeGurukul OTP",
            "destination" => $destinationNumber,
            "userName" => "$name",
            "templateParams" => ["$otp"],
            "source" => "LifeGurukul",
            "buttons" => [
                [
                    "type" => "button",
                    "sub_type" => "url",
                    "index" => 0,
                    "parameters" => [
                        [
                            "type" => "text",
                            "text" => "$otp",
                        ],
                    ],
                ],
            ],
            "carouselCards" => [],
            "location" => ["23.0293504", "72.5680128"],
        ];

        $url = "https://backend.aisensy.com/campaign/t1/api/v2";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, $data);
        // "location" => new stdClass(),

    }
}

/**
 * get mobile Number with country code
 * @param = mobile
 * @param = countryCode
 * @return mobileNumber (+918745123654)
 */
if (!function_exists('getMobileNumber')) {
    function getMobileNumber($mobile, $country_code)
    {
        if (isset($mobile) && !empty($country_code)) {

            $code = Countries::where('id', $country_code)->pluck('phonecode')->first();
            return '+' . $code . $mobile;
        }
        return null;
    }
}

if (!function_exists('numberToWords')) {
    function numberToWords($number)
    {
        $numberToWords = new NumberToWords();

        // build a new currency transformer
        $numberTransformer = $numberToWords->getNumberTransformer('en');

        $words = $numberTransformer->toWords($number);
        return strtoupper($words);

        if ($number == "FREE") {
            echo "Zero Rupees Only";
        } else {
            $words = array(
                '0' => '', '1' => 'One', '2' => 'Two',
                '3' => 'Three', '4' => 'Four', '5' => 'Five', '6' => 'Six',
                '7' => 'Seven', '8' => 'Eight', '9' => 'Nine',
                '10' => 'Ten', '11' => 'Eleven', '12' => 'Twelve',
                '13' => 'Thirteen', '14' => 'Fourteen',
                '15' => 'Fifteen', '16' => 'Sixteen', '17' => 'Seventeen',
                '18' => 'Eighteen', '19' => 'Nineteen', '20' => 'Twenty',
                '30' => 'Thirty', '40' => 'Forty', '50' => 'Fifty',
                '60' => 'Sixty', '70' => 'Seventy',
                '80' => 'Eighty', '90' => 'Ninety',
            );
            $digits = array('', '', 'Hundred', 'Thousand', 'Lakh', 'Crore');

            $number = explode(".", $number);
            $result = array("", "");
            $j = 0;

            foreach ($number as $val) {
                // loop each part of number, right and left of dot
                for ($i = 0; $i < strlen($val); $i++) {
                    // look at each part of the number separately  [1] [5] [4] [2]  and  [5] [8]

                    $numberpart = str_pad($val[$i], strlen($val) - $i, "0", STR_PAD_RIGHT);

                    // make 1 => 1000, 5 => 500, 4 => 40 etc.
                    if ($numberpart <= 20) { // if it's below 20 the number should be one word
                        $numberpart = 1 * substr($val, $i, 2); // use two digits as the word
                        $i++; // increment i since we used two digits

                        $result[$j] .= $words[$numberpart] . " ";
                    } else {
                        //echo $numberpart . "<br>\n"; //debug
                        if ($numberpart > 90) { // more than 90 and it needs a $digit.
                            $result[$j] .= $words[$val[$i]] . " " . $digits[strlen($numberpart) - 1] . " ";
                        } else if ($numberpart != 0) { // don't print zero
                            $result[$j] .= $words[str_pad($val[$i], strlen($val) - $i, "0", STR_PAD_RIGHT)] . " ";
                        }
                    }
                }
                $j++;
            }

            if (trim($result[0]) != "") {
                echo $result[0] . "Rupees ";
            }

            if ($result[1] != "") {
                echo $result[1] . "";
            }

            echo " Only";
        }

        return $number;
    }
}
