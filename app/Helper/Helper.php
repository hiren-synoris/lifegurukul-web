<?php

namespace App\Helper;

use App\Models\User;
use App\Models\Media;
use App\Models\Cities;
use App\Models\Course;
use App\Models\States;
use App\Models\Chapter;
use App\Models\Learner;
use App\Models\UserCoin;
use App\Models\Countries;
use App\Models\CoursePlan;
use App\Models\UserCourse;
use App\Models\ChapterInfo;
use App\Models\RatingReview;
use App\Models\CoursePackage;
use App\Models\DropdownOption;
use App\Models\UserCourseProgress;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Helper
{
    /**
     * @return Date format like DD/MM/YYYY H:i:s(20/10/2022 10:20:00)
     */
    public static function date_format($date)
    {
        return !is_null($date) ? date("d/m/Y H:i:s", strtotime($date)) : '';
    }
    public static function only_date_format($date)
    {
        return !is_null($date) ? date("d/m/Y", strtotime($date)) : '';
    }

    public static function getReview()
    {
        $rating_reviews = RatingReview::select("id", "rating", "comment", "is_approve", "created_at", "learner_id", "course_id")->with(["learner:id,name", "course:id,title"])
            ->orderBy('id', 'desc')
            ->first();

        $rating_reviews->course_name =$rating_reviews->course->title;
        $rating_reviews->name = $rating_reviews->learner->name;
        unset($rating_reviews->learner_id);
        unset($rating_reviews->course_id);
        unset($rating_reviews->course);
        unset($rating_reviews->learner);

        return response()->json([$rating_reviews]);

    }


    public static function BuySellCourse($course_id,$learner_id)
    {
        $userCourse = UserCourse::where("course_id",$course_id)->where("learner_id",$learner_id)->orderBy("id","desc")->first();
        if($userCourse) {
            return isUserCourseExpired($userCourse->expire_at);
        } else {
            return false;
        }

    }

    /**
     * Active/Inactive  status on Index/List page
     * @return Active/In_Active with color(Default 0 Means In_Active && bg-danger)
     */

    public static function checkStatus($status = 0)
    {
        $status = isset($status) && !empty($status) && $status == 1 ? 'Active' : 'In Active';
        $class = isset($status) && $status == 'Active' ? 'bg-success' : 'bg-danger';
        return '<span class="badge rounded-pill ' . $class . '">' . $status . '</span>';
    }

    public static function getCountries($countryId = 0)
    {
        if (isset($countryId) && !empty($countryId)) {
            /**
             * @return Collection (Single country)
             */
            return Countries::whereNull('deleted_at')->where('id', $countryId)->select(["name", 'phonecode', "id", 'flag'])->first();
        }
        return Countries::whereNull('deleted_at')->orderBy('id', 'asc')->get(["name", 'phonecode', "id", 'flag']);
    }

    public static function getStates($countryId = 0)
    {
        if ($countryId > 0) {
            return States::whereNull('deleted_at')->where('country_id', $countryId)->get(["name", "id"]);
        }
        return null;
    }

    public static function getCities($stateId = 0)
    {
        if ($stateId > 0) {
            return Cities::whereNull('deleted_at')->where('state_id', $stateId)->get(["name", "id"]);
        }
        return null;
    }

    /**
     * @return Learner data if learner loggedIn otherwise return 0
     */
    public static function getLearnerData($learnerId)
    {
        if ($learnerId) {
            return Learner::with('country:id,phonecode')->findOrFail($learnerId);
        }
        return [];
    }

    public static function profileImage()
    {
        if (isLearnerLoggedIn()) {
            $profile_pic = Learner::findOrFail(authLearnerID())->profile_pic;
            return $profile_pic ? Storage::url($profile_pic) : URL::asset('/front/img/user/user11.jpg');
        }
        return '';
    }

    public static function asseturl($path = null, $storage = false)
    {
        if (empty($path)) {
            return false;
        }
        return $storage ? asset(Storage::url($path)) : asset($path);
    }
    // get order for new chapter
    public static function setOrder($parentChapterID = 0, $isParent = 0)
    {
        if ($parentChapterID > 0) {
            if ($isParent == 0) {
                $orderData = Chapter::where('parent_id', $parentChapterID)->select('order')->max('order');
            }
            if ($isParent == 1) {
                $orderData = Chapter::where('parent_id', 0)->where('course_id', $parentChapterID)->select('order')->max('order');
            }

            if ($orderData > 0) {
                return ++$orderData;
            } else {
                return 0;
            }
        }
    }

    // set type wise Icon
    public static function setTypeWiseIcon($type = 0, $front = 0)
    {
        if ($front == 1) {
            if (Chapter::FLAG_VIDEO == $type) {
                echo '<img src="' . URL::asset("/front/img/icon/chapter/utube.png") . '" alt="" class="me-2"/> ';
            } elseif (Chapter::FLAG_AUDIO == $type) {
                echo '<i class="fa-solid fa-file-audio"></i> ';
            } elseif (Chapter::FLAG_PDF == $type) {
                echo '<img src="' . URL::asset("/front/img/icon/chapter/pdf.png") . '" alt="" class="me-2"/> ';
            } elseif (Chapter::FLAG_FILE == $type) {
                echo '<img src="' . URL::asset("/front/img/icon/chapter/download.png") . '" alt="" class="me-2"/> ';
            } elseif (Chapter::FLAG_HEADING == $type) {
                echo '<i class="fa-solid fa-heading"></i> ';
            } elseif (Chapter::FLAG_TEXT == $type) {
                echo '<i class="fa-solid fa-file-lines"></i> ';
            } elseif (Chapter::FLAG_LINK == $type) {
                echo '<i class="fa-solid fa-link"></i> ';
            } elseif (Chapter::SELL_BUY == $type) {
                echo '<i class="fa-solid fa-film"></i> ';
            } elseif (Chapter::FLAG_IMAGE == $type) {
                echo '<i class="fa-solid fa-image"></i> ';
            } else {
                echo '<i class="fa-solid fa-file"></i> ';
            }
        } else {
            if (Chapter::FLAG_VIDEO == $type) {
                echo '<i class="fas fa-video"></i> ';
            } elseif (Chapter::FLAG_AUDIO == $type) {
                echo '<i class="fa fa-file-audio"></i> ';
            } elseif (Chapter::FLAG_PDF == $type) {
                echo '<i class="fa fa-file-pdf"></i> ';
            } elseif (Chapter::FLAG_FILE == $type) {
                echo '<i class="fa fa-file"></i> ';
            } elseif (Chapter::FLAG_HEADING == $type) {
                echo '<i class="fa fa-heading"></i> ';
            } elseif (Chapter::FLAG_TEXT == $type) {
                echo '<i class="fa fa-file"></i> ';
            } elseif (Chapter::FLAG_LINK == $type) {
                echo '<i class="fa fa-link"></i>';
            } elseif (Chapter::SELL_BUY == $type) {
                echo '<i class="fa fa-film"></i> ';
            } elseif (Chapter::FLAG_IMAGE == $type) {
                echo '<i class="fa fa-image"></i> ';
            } else {
                echo '<i class="fa fa-file"></i> ';
            }
        }
    }

    // get order for new chapter
    public static function setActiveCourse($chapterID = 0)
    {
        if (isset($chapterID) && !empty($chapterID) && $chapterID > 0) {
            return Chapter::where('id', $chapterID)->select('parent_id')->max('parent_id');
        }
        return 0;
        // if($chapterID > 0){
        //     $parentId= Chapter::where('id',$chapterID)->select('parent_id')->max('parent_id');
        //     if($parentId > 0){
        //         return $parentId;
        //     }
        //     else{
        //         return 0;
        //     }
        // }
        // return 0;

    }

    // set type wise Flag for validation
    public static function setTypeWiseTextBox($chapterID = 0, $onlyFlag = 1)
    {
        $type = null;
        if ($chapterID > 0) {
            $type = Chapter::where('id', $chapterID)->select('asset_type')->max('asset_type');
            if ($type >= 0) {
                $type = $type;
            } else {
                $type = null;
            }
        }
        if ($type >= 0 && $onlyFlag == 0) {
            if (Chapter::FLAG_VIDEO == $type) {
                echo '<input type="hidden" name="video_type" value="1">';
            } elseif (Chapter::FLAG_AUDIO == $type) {
                echo '<input type="hidden" name="audio_type" value="1">';
            } elseif (Chapter::FLAG_PDF == $type) {
                echo '<input type="hidden" name="pdf_type" value="1">';
            } elseif (Chapter::FLAG_FILE == $type) {
                echo '<input type="hidden" name="file_type" value="1">';
            } elseif (Chapter::FLAG_HEADING == $type) {
                echo '<input type="hidden" name="heading_type" value="1">';
            } elseif (Chapter::FLAG_TEXT == $type) {
                echo '<input type="hidden" name="text_type" value="1">';
            } elseif (Chapter::FLAG_LINK == $type) {
                echo '<input type="hidden" name="link_type" value="1">';
            } elseif (Chapter::SELL_BUY == $type) {
                echo '<input type="hidden" name="sellbuy_type" value="1">';
            } elseif (Chapter::FLAG_IMAGE == $type) {
                echo '<input type="hidden" name="image_type" value="1">';
            } else {
            }
        }
        if ($type >= 0 && $onlyFlag == 1) {
            if (Chapter::FLAG_VIDEO == $type) {
                return 1;
            } elseif (Chapter::FLAG_AUDIO == $type) {
                return 1;
            } elseif (Chapter::FLAG_PDF == $type) {
                return 1;
            } elseif (Chapter::FLAG_FILE == $type) {
                return 1;
            } elseif (Chapter::FLAG_HEADING == $type) {
                return 0;
            } elseif (Chapter::FLAG_TEXT == $type) {
                return 1;
            } elseif (Chapter::FLAG_LINK == $type) {
                return 1;
            } elseif (Chapter::SELL_BUY == $type) {
                return 1;
            } elseif (Chapter::FLAG_IMAGE == $type) {
                return 1;
            } else {
                return 0;
            }
        }
    }
    public static function getTypeBasedOnChapterId($chapterID = 0)
    {
        $type = null;
        if ($chapterID > 0) {
            $type = Chapter::where('id', $chapterID)->select('asset_type')->max('asset_type');
            if ($type >= 0) {
                return $type;
            } else {
                return null;
            }
        }
        return $type;
    }
    public static function thumbnailImage($id)
    {
        if ($id > 0) {
            $image = ChapterInfo::findOrFail($id);
            $thumbnail = $image->thumbnail;
            return $thumbnail ? Storage::url($thumbnail) : URL::asset('/admin/dist/img/videoCover.png');
        }
    }

    /**
     * Active/Inactive  status on Index/List page
     * @return PUBLISHED/UNPUBLISHED with color(Default 0 Means UNPUBLISHED && bg-danger)
     */

    public static function checkPublish($status)
    {
        $status = isset($status) && !empty($status) && $status == 1 ? 'PUBLISHED' : 'UNPUBLISHED';
        $class = isset($status) && $status == 'PUBLISHED' ? 'bg-success' : 'bg-danger';
        return '<span class="badge rounded-pill ' . $class . '">' . $status . '</span>';
    }

    /**
     * Get All image URL for APIs
     */
    public static function getImageUrl($url)
    {
        if (strpos($url, 'storage') !== false) {
            $storage_url = $url;

        } else {
            $path = storage_path('app/public/' . $url);

            if (file_exists($path)) {
                $url = Storage::url($url);


                if (strpos($url, env('APP_URL')) !== false) {
                    $storage_url = $url;

                } else {
                    $storage_url = env('APP_URL') . $url;
                }


            } else {
                if (str_contains($url, 'front/img/')) {
                    $storage_url = asset($url);
                } else {
                    $url = 'front/img/' . $url;
                    $storage_url = asset($url);
                }
            }
            return $storage_url;
        }

        return $storage_url;
    }
    /**
     * Get All image URL for APIs
     */
    public static function apiResonse($status, $message, $response = array(), $is_paginated = false)
    {
        $res = self::nullToEmptyStringHelper($response);
        if ($is_paginated) {

            $data = new \stdClass();
            $data = $res;
            $status_message = collect(['status' => (string) $status, 'message' => $message]);
            $data = $status_message->merge($data);
        } else {

            $data['status'] = (string) $status;
            $data['message'] = $message;
            $data['data'] = $res;
            // $data = Arr::prepend($data, $message, 'message');
            // $data = Arr::prepend($data, $status, 'status');
        }
        return $data;
    }

    /**
     * Get All image URL for APIs
     */
    public static function apiResonseOriginal($status, $message, $response = array(), $is_paginated = false)
    {
        $res = $response;
        if ($is_paginated) {

            $data = new \stdClass();
            $data = $res;
            $status_message = collect(['status' => (string) $status, 'message' => $message]);
            $data = $status_message->merge($data);
        } else {

            $data['status'] = (string) $status;
            $data['message'] = $message;
            $data['data'] = $res;
            // $data = Arr::prepend($data, $message, 'message');
            // $data = Arr::prepend($data, $status, 'status');
        }
        return $data;
    }

    /**
     * Active/Inactive  status on Index/List page
     * @return On/Off with color(Default 0 Means Off && bg-danger)
     */

    public static function checkEnable($status = 0)
    {
        $status = isset($status) && !empty($status) && $status == 1 ? 'On' : 'Off';
        $class = isset($status) && $status == 'On' ? 'bg-success' : 'bg-danger';
        return '<span class="badge rounded-pill ' . $class . '">' . $status . '</span>';
    }

    /**
     * Course/Package  status on Index/List page
     * @return Course/Package with color(Default 0 Means Off && bg-danger)
     */
    public static function checkType($status = 0)
    {

        $status = isset($status) && !empty($status) && $status == 1 ? 'Course' : 'Package';
        $class = isset($status) && !empty($status) && $status == 'Course' ? 'bg-success' : 'bg-warning';
        return '<span class="badge rounded-pill ' . $class . '">' . $status . '</span>';
    }

    public static function nullToEmptyStringHelper($request)
    {
        // if (is_array($request)) {
        //     return  array_map(array(self::class, 'nullToEmptyStringHelper'), $request);
        // }
        // else {
        //     if (gettype($request) !== "object") {
        //         return ($request === NULL || $request === null) ? "" : (string)($request);
        //     }
        //     else{
        //         $vars = get_object_vars($request);
        //         foreach ($request as $key => $value) {
        //             if (is_array($value)) {
        //                 $request->$key = array_map(array(self::class, 'nullToEmptyStringHelper'), $value);
        //             } else {
        //                 if ($value === NULL || $value === null) {
        //                     $request->$key = "";
        //                 }
        //                 else{
        //                     $request->$key = (string)($value);
        //                 }
        //             }
        //         }
        //     }
        //     return $request;
        // }
        if (isset($request) && !empty($request)) {
            if (is_array($request)) {
                if (count($request) > 0) {
                    return $request;
                }
            } else {
                if ($request->count() > 0) {
                    $modelAsArray = json_decode(json_encode($request), true);
                    // echo "<pre>";print_r($modelAsArray);die;
                    array_walk_recursive($modelAsArray, function (&$item, $key) {
                        // echo $key."--->".$item."<br>\n";
                        if (getType($item) == "array") {
                            if (setType($item, "object")) {
                                $item = $item;
                            }
                        } else if (getType($item) == "boolean") {
                            $item = (bool) $item;
                        } else if (getType($item) == "string") {
                            $item = (string) $item;
                        } else if (is_int($item) && $item === 0) {
                            $item = "0";
                        } else {
                            $item = ($item === '0') ? false : ($item == null ? "" : (is_bool($item) ? self::checkIsBoolean($item, $key) : (is_string($item) ? $item : (string) $item)));
                        }

                        // if($item == '(object)[]' ){
                        //     $item = (object)[];
                        // } else{
                        // }
                    });

                    return $modelAsArray;
                }
            }
        } else {
            return [];
        }
    }

    public static function checkIsBoolean($item, $key)
    {

        if ($item == false || $item === "0") {
            if ($key == 'is_wishlisted') {
                $bool = false;
            }
        } else {
            return (bool) true;
        }
    }

    public static function getCourseUpdatedDate($courseID = 0, $date = '')
    {
        $updated_at = $date;
        if ($courseID > 0) {
            $res = Chapter::where('course_id', $courseID)->select('updated_at')->max('updated_at');
            if ($res) {
                return !empty($res) ? date('d/m/Y', strtotime($res)) : '';
            } else {
                return $updated_at;
            }
        }
        return $updated_at;
    }
    public static function getPackageUpdatedDate($courseID = 0, $date = '')
    {
        $updated_at = $date;
        if ($courseID > 0) {
            $res = CoursePackage::where('package_id', $courseID)->select('updated_at')->max('updated_at');
            if ($res) {
                return !empty($res) ? date('Y/m/d', strtotime($res)) : '';
            } else {
                return $updated_at;
            }
        }
        return $updated_at;
    }

    public static function pageCount($path)
    {
        $num = 0;
        // $path = 'https://www.africau.edu/images/default/sample.pdf';
        // dd($path);
        $pdftext = file_get_contents($path);
        $num = preg_match_all("/\/Page\W/", $pdftext, $dummy);
        return $num;
        // return "(".$num." pages)";
    }

    public static function duration($chapterId)
    {
        $duration = 0;
        if ($chapterId > 0) {
            try {
                //code...
                $duration = ChapterInfo::where('chapter_id', $chapterId)->pluck('duration')->whereNull('deleted_at')->firstOrFail();
                $data = explode(":",$duration);
            } catch (\Throwable $th) {
                //throw $th;
            }
            if ((int) $duration > 0) {
                // dd($duration);
                if (isset($duration)) {
                    // return '('.gmdate("H:i:s", 2000).')' ; // return Hour:Minute:Second format

                    return $data[0]." Minutes". ":".$data[1]." Seconds";
                }
                // return  "(" . round($duration) . ":00)";
                return $duration;
            }



        }

        return $duration;
    }

    // public static function getInstructure($instructureId = 0)
    // {
    //     if($instructureId > 0){
    //         return Instructors::whereNull('deleted_at')->where('id',$instructureId)->get(["name",'profile_pic',"designation",'id'])->first();
    //     }
    //     return  null;
    // }
    public static function getMediaCourse($mediaId = 0)
    {
        if ($mediaId > 0) {
            $coursedata = Media::where('media.id', $mediaId)
                ->join('chapters', 'media.id', 'chapters.media_id')
                ->join('courses', 'chapters.course_id', 'courses.id')
                ->select(DB::raw('group_concat(courses.title) as course'))
                ->groupBy('media.id')->first();
            return $coursedata->course ?? '';
        }
        return null;
    }

    public static function getDeviceType($deviceType)
    {
        // dd($deviceType);
        if ($deviceType == '1') {
            $deviceType = Course::COURSE_ANDROID;
        }

        if ($deviceType == '2') {
            $deviceType = Course::COURSE_IOS;
        }

        if ($deviceType == '3') {
            $deviceType = Course::COURSE_WEBSITE;
        }

        return $deviceType ?? '';
    }

    public static function getChapterAssetType($asset_type)
    {

        $asset = $asset_type == 0 ? 'Video' : ($asset_type == 1 ? 'Audio' : ($asset_type == 2 ? 'PDF' : ($asset_type == 3 ? 'File' : ($asset_type == 4 ? 'Heading' : ($asset_type == 5 ? 'Text' : ($asset_type == 6 ? 'Link' : ($asset_type == 7 ? 'Sell_&_buy' : ($asset_type == 8 ? 'Image' : ''))))))));

        return $asset ?? '';
    }
    public static function getCourseLanguage($course_lang)
    {
        switch ($course_lang) {
            case '2':
                $language = 'Hindi';
                break;
            case '3':
                $language = 'Hindi and English';
                break;
            default:
                $language = 'English';
                break;
        }
        return $language ?? '';

    }
    public static function checkFree($course_id, $learner_id)
    {
        $order = UserCourse::where("course_id", $course_id)
            ->where("learner_id", $learner_id)
            ->orderByDesc("id")
            ->first();

        if ($order) {
            // Return true if order_status is 3, false otherwise
            return $order->order_status == 3;
        }

        // Return false if no order was found
        return false;
    }
    public static function getCategoryName($id)
    {
        if ($id > 0) {
            $data = DropdownOption::findOrFail($id);
            $name = $data->name;
            return $name ?? '';
        }
    }
    public static function getInstructure($instructureId = 0)
    {

        $role = [User::INSTRUCTOR];
        $query = DB::table('model_has_roles')->join('users', 'users.id', 'model_has_roles.model_id')
            ->join('roles', 'roles.id', 'model_has_roles.role_id')
            ->join('instructors', 'instructors.user_id', 'users.id')
            ->select('users.id', 'users.name', 'users.email', 'users.profile_picture', 'users.created_at AS created_at', 'users.deleted_at', 'model_has_roles.role_id', DB::raw('GROUP_CONCAT(roles.name SEPARATOR ", ") as rolesName'), 'instructors.designation', 'instructors.bio', 'instructors.facebook_follower', 'instructors.instagram_follower', 'instructors.youtube_follower', 'instructors.twitter_follower', 'instructors.id as instructorsId')
            ->whereNull('users.deleted_at')
            ->whereIn('roles.name', $role);

        return $query->groupBy("users.id")->get();
    }

    public static function getInstructorDeleted($instructureId = 0)
    {
        $role = [User::INSTRUCTOR];
        $query = DB::table('model_has_roles')->join('users', 'users.id', 'model_has_roles.model_id')
            ->join('roles', 'roles.id', 'model_has_roles.role_id')
            ->join('instructors', 'instructors.user_id', 'users.id')
            ->select('users.id', 'users.name', 'users.email', 'users.profile_picture', 'users.created_at AS created_at', 'users.deleted_at', 'model_has_roles.role_id', DB::raw('GROUP_CONCAT(roles.name SEPARATOR ", ") as rolesName'), 'instructors.designation', 'instructors.bio', 'instructors.facebook_follower', 'instructors.instagram_follower', 'instructors.youtube_follower', 'instructors.twitter_follower', 'instructors.id as instructorsId')
            ->whereNotNull('users.deleted_at')
            ->whereIn('roles.name', $role);
        if ($instructureId > 0) {
            $query->where('instructors.id', $instructureId);
        }
        return $query->groupBy("users.id")->latest()->get();
    }

    public static function getAllowedCourse($learnerId = 0, $courseID = 0)
    {

        if ($courseID > 0) {
            UserCourse::updateOrCreate(
                [
                    'learner_id' => $learnerId,
                    'course_id' => $courseID,
                ],
                [
                    'order_status' => 1,
                ]
            );
        } /*else{
    $course = Course::where("is_freely_avil_learner",1)
    ->whereIn('course_platform', [Course::COURSE_ALL, Course::COURSE_WEBSITE])
    ->where('courses.status', 1)
    ->select('id')
    ->get();
    if($learnerId > 0 && isset($course) && !empty($course)){
    foreach($course as $val){
    UserCourse::updateOrCreate(
    [
    'learner_id' => $learnerId,
    'course_id' => $val->id
    ],
    [
    'order_status' => 1
    ]
    );
    }
    }
    }*/
    }

    /**
     * Course/Package  status on Index/List page
     * @return Course/Package with color(Default 0 Means Off && bg-danger)
     */
    public static function checkTransactionType($status = 0)
    {

        $status = isset($status) && !empty($status) && $status == 1 ? 'Success' : 'Failure';
        $class = isset($status) && !empty($status) && $status == 'Success' ? 'bg-success' : 'bg-danger';
        return '<span class="badge rounded-pill ' . $class . '">' . $status . '</span>';
    }

    public static function checkNewTransactionType($status = 0)
    {

        $status = isset($status) && !empty($status) && $status == 1 ? 'Success' : 'Failure';
        $class = isset($status) && !empty($status) && $status == 'Success' ? 'bg-success' : 'bg-danger';
        return '<span class="badge rounded-pill ' . $class . '">' . $status . '</span>';
    }

    /**
     * Course/Package  status on Index/List page
     * @return Course/Package with color(Default 0 Means Off && bg-danger)
     */
    public static function getcourseData($course)
    {
        $array = [];
        if (!is_null($course)) {
        }
        return $course;
    }

    public static function getCourseTitle($courseId)
    {
        $courseTitle = 0;
        if ($courseId > 0) {
            $courseTitle = Course::where('id', $courseId)->pluck('title')->first();
            if (!empty($courseTitle)) {
                return $courseTitle;
            }
        }
        return $courseTitle;
    }

    public static function getCoursePlan($courseId = 0)
    {
        if ($courseId > 0) {
            return CoursePlan::whereNull('deleted_at')->where('course_id', $courseId)->get(["plan_name", "id", "final_payable_price"]);
        }
        return null;
    }

    public static function get_course_detail_for_api($courseId, $planId = null, $device_type = null)
    {
        // dd($courseId);
        if (!empty($courseId) && !is_null($courseId)) {
            // dd($courseId);
            $deviceType = Course::COURSE_WEBSITE;
            $course = Course::select('id', 'title', 'instructor_id', 'image', 'lng', 'hours', 'minutes', 'courses.image', 'type', 'show_learner_cnt')

            //->whereIn('course_platform', [Course::COURSE_ALL, $device_type])
                ->whereRaw("find_in_set($deviceType , course_platform)")
                ->with(['instructor' => function ($query) {
                    $query->select('name', 'id', 'profile_picture');
                }, 'instructor.instructure:user_id,designation', 'rating_reviews' => function ($query) {
                    return $query->where('is_approve', true)->select('course_id', 'rating');
                }])
            // ->with(['plans' => function ($query) use ($courseId) {
            //     $query->where('course_id', $courseId)->where('status',1);
            // }])
                ->withCount([
                    'rating_reviews as total_review',
                ])
                ->withAvg('rating_reviews as rating', 'rating')
                ->withCount(['chapters' => function ($query) {
                    $query->where('parent_id', 0);
                }])
                ->withCount(['packages'])
                ->whereNull('deleted_at')
                ->where('id', $courseId);

            if (!empty($planId) && !is_null($planId)) {
                $course->with(['plans' => function ($query) use ($planId, $courseId) {
                    $query->where('id', $planId)->where('course_id', $courseId);
                }]);
            }
            // $course->image = !empty($course->image) ? Helper::getImageUrl($course->image) : '';

            $course_data = $course->first();
            //

            // dd($course_data->plans);

            // $course_data->image = !empty($course_data->image) ? Helper::getImageUrl($course_data->image) : '';

            if (!empty($course_data)) {
                if (!empty($course_data->plans)) {
                    $course_data->list_price = !empty($course_data['plans'][0]['list_price']) ? $course_data['plans'][0]['list_price'] : '';
                }
                if (!empty($course_data->plans)) {
                    $course_data->final_payable_price = !empty($course_data['plans'][0]['final_payable_price']) ? $course_data['plans'][0]['final_payable_price'] : '';

                    $course_data->productId = !empty($course_data['plans'][0]['renewing_subscriptions_id']) ? $course_data['plans'][0]['renewing_subscriptions_id'] : '';
                }

                unset($course_data->plans);
                $course_data->show_learner_cnt = $course_data->show_learner_cnt == 1 ? true : false;
                $learners_data = UserCourse::select('id')->where('course_id', $course_data->id)->get();
                $course_data->learner_count = empty(count($learners_data)) ? '0' : count($learners_data);
                $course_data->image = isset($course_data->image) && !empty($course_data->image) ? Helper::getImageUrl($course_data->image) : '';
                if (!empty($course_data->instructor_id)) {
                    $course_data->instructor->profile_picture = !empty($course_data->instructor->profile_picture) ? Helper::getImageUrl($course_data->instructor->profile_picture) : '';
                }
                $course_data->packages_count = 0;
                if ($course_data->type == 2) {
                    $course_data->packages_count = Helper::get_package_course_count($course_data->id, $deviceType);
                }
            }
            return $course_data;
        }
        return collect();
    }
    // public static function course_min_max_price($id=NULL)
    // {
    //     $min = 0;
    //     $max = 0;
    //     if(is_null($id)) return false;
    //     try {
    //         $course = Course::where('id',$id)->with(['plans'=>function($query){
    //             return $query->where('status',1)->where('plan_type', '<>', 2)->orderBy('order','Asc');
    //         }])->first();
    //         $min=$course->plans->pluck('final_payable_price')->min() ?? '0.00';
    //         $max=$course->plans->where('final_payable_price',$min)->filter(function($value, $key) use($min){
    //             return $value->final_payable_price == $min;
    //         })->pluck('list_price')->min() ?? '0.00';
    //     } catch (\Throwable $th) {}
    //     return [
    //         'final_payable_price' => $min,
    //         'list_price' => $max
    //     ];

    // }
    public static function get_course_common_api_data($course, $request , $isPurchase = null)
    {

        $sum = 0;
        $course->getChapters->map(function ($v) use ($course, &$sum) {
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

        $expireFlag = is_expired($course?->userCourse?->where([["learner_id", $request->user_id], ["course_id", $course->id]])->first()->expire_at ?? '');
        $course->expire_at = $course?->userCourse?->where([["learner_id", $request->user_id], ["course_id", $course->id]])->first()->expire_at ?? '';
        $course->is_expire = false;
        $course->validity = '';
        $course->course_purchase_id = $course?->userCourse->id ?? '';
        if ($expireFlag == 0) {
            $course->is_expire = true;
            $course->validity = 'Expired';
        } else if ($expireFlag == 1) {
            $course->is_expire = false;
            $course->validity = dateFormate($course->expire_at) . ' Days';
        } else if ($expireFlag == 2) {
            $course->is_expire = false;
            $course->validity = 'Lifetime';
        }

        if (!empty($course->chaptersCounts->first()) && !empty($course->chaptersCounts->first()->chapterIds)) {
            // dd($course->chapters);
            $chapterIds = explode(",", $course->chaptersCounts->first()->chapterIds);
            // dd($chapterIds);
            $totalChapters = count($chapterIds);
            $totalCompletedChapter = UserCourseProgress::selectRaw('sum(is_completed) as watched_times')->whereIn('chapter_id', $chapterIds)

                ->where("learner_id", $request->user_id)
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

        $scope = $request->scope;
        $has_wishlist = ($scope == 'learner') ? true : false;
        $course->is_wishlisted = isset($course->wishlists) && $course->wishlists->count() > 0 && $has_wishlist ? true : false;
        $user = !empty($course->instructor_id) ? @User::find($course->instructor_id) : '';
        $course->instructor_name = $user->name ?? '';
        $course->designation = $user->designation ?? '';
        $course->isCombineCourse = $course->type == '1' ? false : true;
        $course->coin_price = config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : "";
        $course->course_coin = isset($course->course_coin) ? $course->course_coin : "";
        $course->lng = Helper::getCourseLanguage($course->lng);
        $course->total_review = !empty($course->rating_reviews) ? count($course->rating_reviews) : 0;
        $course->rating = empty($course->rating) ? '0' : $course->rating;
        $course->hours = $course->hours;
        $course->minutes = $course->minutes;
        $course->total_enroll = empty($course->total_enroll) ? '0' : $course->total_enroll;
        $course->learners_data = UserCourse::select('id')->where('course_id', $request->course_id)->get();
        $course->learner_count = empty(count($course->learners_data)) ? '0' : count($course->learners_data);
        $course->show_learner_cnt = ($course->show_learner_cnt == 1) ? true : false;
        // $course->allow_offline_data = ($course->allow_offline_data == 1) ? True : False;

        // $course->is_purchased = $course->total_enroll > '0' ? true : false;

        $course->is_course_purchased = false; // Set 'false' to default value
        $course->course_purchase_id = ''; // Set blank(empty) value
        $coin = UserCoin::where("learner_id", $request->user_id)->get();
        // if ($coin) {
        //     $coin_redeem = $coin->where("type", "2")->sum("coins");
        //     $coin_earn = $coin->where("type", "1")->sum("coins");
        //     $total = $coin_earn - $coin_redeem;
        //     $course->user_coin = $total;
        // } else {
        //     $course->user_coin = 0;
        // }

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

        if ($course->userCourseExpectedRelationship->count() > 0) {
            // dd($course->userCourseExpectedRelationship);
            foreach ($course->userCourseExpectedRelationship as $userCourse) {
                $data = UserCourse::where("course_id", $userCourse->course_id)->orderBy("id", "desc")->where("learner_id", request()->user_id)->first();
                if ($data) {
                    if (isUserCourseExpired($data->expire_at) == true) {
                        $course->is_course_purchased = isUserCourseExpired($data->expire_at);
                        $course->course_purchase_id = isUserCourseExpired($data->expire_at) == true ? $data->id : '';
                    } else {
                        $course->is_course_purchased = false;
                        $course->course_purchase_id = '';
                    }
                }
            }

           } else {

            // if($course->getPackages->count() > 0) {
            // dump($course->getPackages->toArray());
            foreach ($course->getPackages as $package) {
                // dump($package->package_id);
                $package_user = UserCourse::where("course_id", $package->package_id)->orderBy("id", "desc")->where("learner_id", request()->user_id)->first();
                if ($package_user) {

                    if (isUserCourseExpired($package_user->expire_at) == true) {
                        $course->is_course_purchased = isUserCourseExpired($package_user->expire_at);
                        $course->course_purchase_id = isUserCourseExpired($package_user->expire_at) == true ? $package_user->id : '';
                        break;
                    }
                } else {

                    $course->is_course_purchased = false;
                    $course->course_purchase_id = '';
                }
            }
            // } else {
            //     $course->is_course_purchased = false;
            //     $course->course_purchase_id = '';
            // }
           }

        // foreach($course->getPackages as $value){
        //     $userCourse = UserCourse::where("course_id",108)->where("learner_id",request()->user_id)->get();

        //     if( $userCourse ) {
        //         foreach($userCourse as $u_course) {
        //             if(isUserCourseExpired($u_course->expire_at)==true){
        //                 // foreach($value->getPackageBasedCourse as $val){
        //                 //     if($val->learner_id == request()->user_id){
        //                     // dump($course->is_course_purchased);
        //                         $course->is_course_purchased = isUserCourseExpired($u_course->expire_at); // get 'true' or 'false'
        //                         $course->course_purchase_id = isUserCourseExpired($u_course->expire_at) == true ? $u_course->id : ''; // get user_courses primary k
        //                 //     }
        //                 // }

        //             } else {
        //                 $course->is_course_purchased = ''; // get 'true' or 'false'
        //                 $course->course_purchase_id = '';
        //             }
        //         }
        //     }
        // }

        // if(isset($course) && isset($course->userCourseExpectedRelationship) && !empty($course->userCourseExpectedRelationship)){

        //     $course->userCourseExpectedRelationship->map(function($item) use ($course){
        // if($item->learner_id == request()->user_id){
        //     $course->is_course_purchased = isUserCourseExpired($item->expire_at); // get 'true' or 'false'
        //     $course->course_purchase_id = $course->is_course_purchased == true ? $item->id : ''; // get user_courses primary key 'id'
        //     return $course;
        // }
        //     });
        //     unset($course->userCourseExpectedRelationship); // unset(remove) relationship key because not required.
        // }

        // 1.  get all package for that course
        // 2. is any above package is purchase by users (user_course)
        // 3. if not empty then check is not expired then only override $course->is_course_purchased = true
        // For package course
            $course->packages_count = 0;
            if ($course->type == 2) {
            // $packageIds = CoursePackage::where('package_id',$course->id)->get()->pluck('package_id');
            // // dump($packageIds);
            // $userCourseData = UserCourse::whereIn('course_id', $packageIds)->where('learner_id', request()->user_id)->get();
            // // dump($userCourseData);
            // if(isset($userCourseData) && !empty($userCourseData)){
            //     $userCourseData->map(function($item, $key) use ($course){
            //        // dump($item->course_id);
            //         if($item->learner_id == request()->user_id){
            //             $course->is_course_purchased = isUserCourseExpired($item->expire_at); // get 'true' or 'false'
            //             $course->course_purchase_id = $course->is_course_purchased == true ? $item->id : ''; // get user_courses primary key 'id'
            //             return $course;
            //         }
            //     });
            // }
            // dd($course);
            // if($course->getPackages->count() > 0) {
            // foreach($course->getPackages as $package) {
            // $package_user = UserCourse::where("course_id",$course->course_id)->where("learner_id",request()->user_id)->first();
            // if($package_user) {
            //     if(isUserCourseExpired($package_user->expire_at)==true){

            //         $course->is_course_purchased = isUserCourseExpired($package_user->expire_at);
            //         $course->course_purchase_id = isUserCourseExpired($package_user->expire_at) == true ? $package_user->id : '';
            //     }
            // } else {
            //     $course->is_course_purchased = false;
            //     $course->course_purchase_id = '';
            // }
            // }
            // }
            // else {
            //     $course->is_course_purchased = false;
            //     $course->course_purchase_id = '';
            // }
            $course->packages_count = Helper::get_package_course_count($course->id, $request->devicetype);

            // $course->course_list = new Collection([]);
            // if ($course && isset($course->packages) && count($course->packages) > 0) {
            //     $course->packages->map(function ($value, $key) use ($course, $request) {
            //         $value->package = Course::select('courses.id', 'title', 'image', 'cp.list_price', 'cp.final_payable_price','cp.plan_type')
            //             ->leftJoin('course_plans as cp', 'cp.id', '=', $request->device_price_field)
            //             ->where('cp.status', '1')
            //             ->where('courses.id', $value->course_id)
            //             ->withAvg('rating_reviews as rating', 'rating')
            //             ->withCount('rating_reviews as total_review')
            //             ->whereNotNull('courses.' . $request->device_price_field)->with([
            //                 'plans' => function ($query) {
            //                     return $query->where('status', 1)->orderBy('order', 'Asc');
            //                 }
            //             ])
            //             ->first();
            //         if (!empty($value->package)) {
            //             $value->package->image = isset($value->package->image) && !empty($value->package->image) ? Helper::getImageUrl($value->package->image) : '';
            //             unset($value->package->plans, $value->package->categories, $value->package->rating_reviews);
            //             $course->course_list->push($value->package);
            //         }
            //     });
            // }
            // $course->packages_count = $course->course_list->count();
            // dd($course->packages_count);
            unset($course->chapters, $course->user, $course->chapters_count, $course->user_course, $course->learners_data);


        }


        // return !$courses->is_course_purchased;

        // $courses = $course->filter(function ($course) {
        //     return !$course->is_course_purchased;
        // })->values();

        $course->image = !empty($course->image) ? Helper::getImageUrl($course->image) : '';
        // dd($course->validity);
        return $course;

    }




    public static function get_package_course_count($package_id, $deviceTyp = '')
    {
        $total_active_courses = CoursePackage::select('course_packages.course_id', "courses.*")->join('courses', 'courses.id', '=', 'course_packages.course_id')->where('package_id', $package_id)

            ->whereNull('course_packages.deleted_at')
            ->whereNull('courses.deleted_at')
            ->whereRaw("find_in_set($deviceTyp , courses.course_platform)")
            // // ->whereRaw("find_in_set(?, course_platform)", [$deviceTyp])
            ->get();
            // dd( $total_active_courses->count());
        return $total_active_courses->count();
    }
    public static function before_buy_get_package_course_count($package_id, $deviceTyp = '')
    {
        $total_active_courses = CoursePackage::select('course_packages.course_id', "courses.*")->join('courses', 'courses.id', '=', 'course_packages.course_id')->where('package_id', $package_id)
            ->whereHas("getPlan",function($q){
                $q->where("status",1);
            })
            ->where('courses.status', '1')
            ->whereNull('course_packages.deleted_at')
            ->whereNull('courses.deleted_at')
            ->whereRaw("find_in_set($deviceTyp , courses.course_platform)")
            ->count();
        return $total_active_courses;
    }

    /**
     * Active/Inactive  toggel
     * @return toggel with color(Default 0 Means In_Active && bg-danger)
     */

    public static function isPriceActive($name, $value, $courseId, $checked = 0, $class = '')
    {
        $check = '';
        if ((int) $checked > 0 && $checked == $value) {
            $check = 'checked="checked"';
        }
        return '<label class="switch ' . $class . '">
                    <input type="radio" class="priceSet" name="' . $name . '" value="' . $value . '" ' . $check . ' data-type="' . $name . '" data-id="' . $value . '" data-courseId="' . $courseId . '">
                    <div class="slider round"></div>
                </label>';
    }

    public static function chapterStatus($id)
    {
        return Chapter::where("id", $id)->first();
    }

    public static function countCategory($id)
    {
        return count(Course::distinct()
                ->whereRaw("find_in_set(3 , course_platform)")
                ->whereHas('categories', function ($query) use ($id) {
                    $query->whereIn('category_id', stringToArray($id))->whereNull('course_categories.deleted_at');
                })
                ->leftJoin('course_plans as cp', 'cp.id', '=', 'courses.default_web_price')
                ->where('courses.status', '1')
                ->where('cp.status', '1')
                ->whereNotNull('courses.default_web_price')
                ->whereNull('courses.deleted_at')
                ->groupBy('courses.id')
                ->get());
    }

    public static function toStringRecursive($data) {
        if (is_array($data) || is_object($data)) {
            // If it's an array or object, convert it to array recursively
            if (is_object($data) && method_exists($data, 'toArray')) {
                $data = $data->toArray();
            } else {
                $data = (array) $data;
            }
            foreach ($data as $key => $value) {
                $data[$key] = Helper::toStringRecursive($value);
            }
            return $data;
        } elseif (is_bool($data)) {
            return $data;
        } else {
            // Convert other types to string, handling null values
            return is_null($data) ? '' : (string) $data;
        }
    }

}
