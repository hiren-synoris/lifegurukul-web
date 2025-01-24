<?php

namespace App\Http\Controllers\admin;

use App\Models\Faq;
use App\Models\Blog;
use App\Models\Page;
use App\Models\Role;
use App\Models\User;
use App\Models\Media;
use App\Models\Slide;
use App\Models\Course;
use App\Models\Slider;
use App\Models\Chapter;
use App\Models\Learner;
use App\Models\Dropdown;
use App\Models\Settings;
use App\Models\UserCoin;
use App\Models\Wishlist;
use App\Models\CoursePlan;
use App\Models\NewsLetter;
use App\Models\Permission;
use App\Models\UserCourse;
use App\Models\CouponUsage;
use App\Models\DeviceToken;
use App\Models\Instructors;

use App\Models\PublicForum;
use App\Models\ChapterInfo;;
use App\Models\Notification;
use App\Models\RatingReview;
use Illuminate\Http\Request;
use App\Models\Emailtemplate;

use App\Models\CourseCategory;
use App\Models\CoursePackage;;
use App\Models\DropdownOption;
use App\Models\PublicForumReply;
use App\Models\UserCourseProgress;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


class BulkDeleteController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }

    public function bulk_Delete(Request $request, $type)
    {

        if ($type == 'users' || $type == "subadmin") {
            // $model = User::class;
            // $notification =  $this->staticBulkDelete($model, 'user', 'Users deleted successfully','No user selected', 'delete_users', $request->bd);
            if (!empty($request->bd) > 0) {
                foreach ($request->bd as $id => $value) {

                    $user = User::where('id', $id)->first();

                    $course = Course::where("instructor_id", $user->id)->get();
                    if ($course) {
                        foreach ($course as $val) {
                            $course_plan = CoursePlan::where("course_id", $id)->get();
                            $course_plan->each->Delete();
                            $course_cat = CourseCategory::where("course_id", $id)->get();
                            $course_cat->each->Delete();
                            $rating = RatingReview::where("course_id", $id)->get();
                            $rating->each->Delete();
                        }
                        $user->Delete();
                        $course->each->Delete();
                    }
                }
                $notification['type'] = "sweet-alert";
                $notification['status'] = "success";
                $notification['title'] = "Success";
                $notification['msg'] = 'Users deleted successfully';
            } else {
                $notification['type'] = "sweet-alert";
                $notification['status'] = "error";
                $notification['title'] = "Error";
                $notification['msg'] = 'No users selected'; //"No user selected";
            }
            return redirect()->back()->with('notification', $notification);
        } elseif ($type == 'roles') {
            $model = Role::class;
            $notification =  $this->staticBulkDelete($model, 'user', 'Roles deleted successfully', 'No Roles selected', 'delete_roles', $request->bd);
        } elseif ($type == 'settings') {
            $model = Settings::class;
            $notification =  $this->staticBulkDelete($model, 'user', 'Settings deleted successfully', 'No Settings selected', 'delete_settings', $request->bd);
        } elseif ($type == 'permissions') {
            $model = Permission::class;
            $notification =  $this->staticBulkDelete($model, 'user', 'Permissions deleted successfully', 'No Permissions selected', 'delete_permissions', $request->bd);
        } elseif ($type == 'dropdowns') {
            $model = Dropdown::class;
            $notification =  $this->staticBulkDelete($model, 'user', 'Dropdown deleted successfully', 'No dropdown selected', 'delete_dropdowns', $request->bd);
        } elseif ($type == 'dropdown_options') {
            $model = DropdownOption::class;
            $notification =  $this->staticBulkDelete($model, 'user', 'Options deleted successfully', 'No dropdown option selected', 'delete_dropdown_options', $request->bd);
        } elseif ($type == 'courses') {
            //dd("");
            if (!empty($request->bd) > 0) {
                foreach ($request->bd as $id => $value) {
                    $course = Course::where('id', $id)->firstOrFail();

                    // ChapterInfo::whereIn('chapter_id', function ($query) use ($id) {
                    //     $query->select('id')
                    //         ->from('chapters')
                    //         ->where('course_id', $id);
                    // })->delete();
                    $chp = Chapter::where("course_id", $id)->get();
                    foreach ($chp as $val) {
                        UserCourseProgress::where('chapter_id', $val->id)->delete();
                        // Chapter::where('course_id', $val->id)->delete();
                    }
                    // Chapter::where('course_id', $id)->delete();
                    CoursePlan::where('course_id', $id)->delete();
                    CoursePackage::where('course_id', $id)->delete();
                    Wishlist::where('course_id', $id)->delete();
                    Notification::where('courseId', $id)->delete();
                    UserCourse::where('course_id', $id)->delete();
                    RatingReview::where('course_id', $id)->delete();
                    CourseCategory::where('course_id', $id)->delete();
                    UserCoin::where('course_id', $id)->delete();
                    $course->delete();
                }

                $notification['type'] = "sweet-alert";
                $notification['status'] = "success";
                $notification['title'] = "Success";
                $notification['msg'] = 'Courses deleted successfully';
            } else {
                $notification['type'] = "sweet-alert";
                $notification['status'] = "error";
                $notification['title'] = "Error";
                $notification['msg'] = 'No courses selected'; //"No user selected";
            }

            return redirect()->back()->with('notification', $notification);
            // $model = Course::class;
            // $notification =  $this->staticBulkDelete($model, 'user', 'Courses deleted successfully','No courses selected', 'delete_courses', $request->bd);
        } elseif ($type == 'pages') {

            $model = Page::class;
            $notification =  $this->staticBulkDelete($model, 'user', 'Pages deleted successfully', 'No pages selected', 'delete_pages', $request->bd);
        } elseif ($type == 'slider') {
            $model = Slider::class;
            $notification =  $this->permantBulkDelete($model, 'user', 'Slider deleted successfully', 'No slider selected', 'delete_slider', $request->bd);
        } elseif ($type == 'media') {

            $keys = array_keys($request->bd);
            $media_vdociper = Media::whereIn("id", $keys)->get("videoId")->toArray();
            $videoIds = array_column($media_vdociper, 'videoId');
            if (!empty($videoIds)) {
                deleteVideo($videoIds);
            }
            $model = Media::class;
            $notification =  $this->staticBulkDelete($model, 'user', 'Media deleted successfully', 'No media selected', 'delete_media', $request->bd);
        } elseif ($type == 'news_letters') {
            $model = NewsLetter::class;
            $notification =  $this->staticBulkDelete($model, 'user', 'News Letters deleted successfully', 'No news letters selected', 'delete_news_letters', $request->bd);
        } elseif ($type == 'blogs') {
            $model = Blog::class;
            $notification =  $this->staticBulkDelete($model, 'user', 'Blogs deleted successfully', 'No news letters selected', 'delete_blog', $request->bd);
        } elseif ($type == 'faq') {
            $model = Faq::class;
            $notification =  $this->staticBulkDelete($model, 'user', 'FAQ deleted successfully', 'No FAQs selected', 'delete_faq', $request->bd);
        } elseif ($type == 'instructors') {
            if (!empty($request->bd) > 0) {
                foreach ($request->bd as $id => $value) {
                    $user = User::where('id', $id)->first();
                    $course = Course::where("instructor_id", $user->id)->get();
                    if ($course) {
                        foreach ($course as $val) {
                            $chapter = Chapter::where("course_id", $val->id)->get();
                            if ($chapter) {
                                foreach ($chapter as $chapt_val) {
                                    ChapterInfo::where("chapter_id", $chapt_val->id)->delete();
                                    UserCourseProgress::where("chapter_id", $chapt_val->id)->delete();
                                }
                            }
                            $course_plan = CoursePlan::where("course_id", $val->id)->get();
                            $course_plan->each->delete();
                            $course_cat = CourseCategory::where("course_id", $val->id)->get();
                            $course_cat->each->delete();
                            $chapter = Chapter::where("course_id", $val->id)->get();
                            $chapter->each->delete();
                            $rating = RatingReview::where("course_id", $val->id)->get();
                            $rating->each->delete();
                            $wishlist = Wishlist::where("course_id", $val->id)->get();
                            $wishlist->each->delete();
                            $user_course = UserCourse::where("course_id", $val->id)->get();
                            $user_course->each->delete();
                        }
                        $user->delete();
                        $course->each->delete();
                    }
                }


                $notification['type'] = "sweet-alert";
                $notification['status'] = "success";
                $notification['title'] = "Success";
                $notification['msg'] = 'Instructor deleted successfully';/* "User deleted successfully"; */
            } else {
                $notification['type'] = "sweet-alert";
                $notification['status'] = "error";
                $notification['title'] = "Error";
                $notification['msg'] = 'No Instructor selected'; //"No user selected";
            }

            return redirect()->back()->with('notification', $notification);

            // $model = User::class;
            // $notification =  $this->staticBulkDelete($model, 'user', 'Instructor deleted successfully','No Instructor selected', 'delete_instructors', $request->bd);
        } elseif ($type == 'learners') {

            // $model = Learner::class;
            // $notification =  $this->staticBulkDelete($model, 'user', 'Learners deleted successfully','No learners selected', 'delete_learners', $request->bd);
            if (!empty($request->bd) > 0) {
                foreach ($request->bd as $id => $value) {
                    $learner = Learner::where('id', $id)->first();
                    $learner->delete();
                    RatingReview::where('learner_id', $id)->delete();
                    UserCourse::where('learner_id', $id)->delete();
                    RatingReview::where("learner_id", $id)->delete();
                    UserCourseProgress::where('learner_id', $id)->delete();
                    Notification::where('learnerId', $id)->delete();
                    Wishlist::where('learner_id', $id)->Delete();
                    DeviceToken::where('learner_id', $id)->delete();
                    $public_forum = PublicForum::where("created_by_learner", $id)->get();
                    $public_forum->each->delete();

                    $public_forum_reply = PublicForumReply::where("reply_by_learner", $id)->get();
                    $public_forum_reply->each->delete();
                }

                $notification['type'] = "sweet-alert";
                $notification['status'] = "success";
                $notification['title'] = "Success";
                $notification['msg'] = 'Learners deleted successfully';
            } else {
                $notification['type'] = "sweet-alert";
                $notification['status'] = "error";
                $notification['title'] = "Error";
                $notification['msg'] = 'No learners selected';
            }


            return redirect()->back()->with('notification', $notification);
        } elseif ($type == 'email-templates') {
            $model = Emailtemplate::class;
            $notification =  $this->staticBulkDelete($model, 'user', 'Email Templates deleted successfully', 'No email templates selected', $request->bd);
            // $notification =  $this->staticBulkDelete($model, 'user', 'Email Templates deleted successfully','No email templates selected', 'delete_email_templates', $request->bd);
        } elseif ($type == 'packages') {

            if (!empty($request->bd) > 0) {
                foreach ($request->bd as $id => $value) {
                    $course = Course::where('id', $id)->firstOrFail();
                    $course->delete();
                    coursePackage::where('id', $id)->delete();
                }
                $notification['type'] = "sweet-alert";
                $notification['status'] = "success";
                $notification['title'] = "Success";
                $notification['msg'] = 'Packages deleted successfully';
            } else {
                $notification['type'] = "sweet-alert";
                $notification['status'] = "error";
                $notification['title'] = "Error";
                $notification['msg'] = 'No packages selected';
            }
            return redirect()->back()->with('notification', $notification);
            // $model = Course::class;
            // $notification =  $this->staticBulkDelete($model, 'user', 'Packages deleted successfully','No packages selected', 'delete_packages', $request->bd);
        } elseif ($type == 'usercourses') {

            // $model = UserCourse::class;
            // $notification =  $this->staticBulkDelete($model, 'user', 'user courses deleted successfully', 'No courses selected', 'delete_courses', $request->bd);

            if (!empty($request->bd) > 0) {
                foreach ($request->bd as $id => $value) {
                    $user_course  = UserCourse::where("id", $id)->first();
                    if($user_course) {

                        $duplicate_learner = UserCourse::where("learner_id", $user_course->learner_id)->where("course_id",$user_course->course_id)->delete();

                        CouponUsage::where("course_id",$user_course->course_id)->where("learner_id",$user_course->learner_id)->delete();
                        UserCoin::where("course_id",$user_course->course_id)->where("learner_id",$user_course->learner_id)->delete();
                        $user_course->delete();
                    }
                }

                $notification['type'] = "sweet-alert";
                $notification['status'] = "success";
                $notification['title'] = "Success";
                $notification['msg'] = 'Learners deleted successfully';
            } else {
                $notification['type'] = "sweet-alert";
                $notification['status'] = "error";
                $notification['title'] = "Error";
                $notification['msg'] = 'No learners selected';
            }

            return redirect()->back()->with('notification', $notification);

        } elseif ($type == 'notifications') {
            $model = Notification::class;
            $notification =  $this->staticBulkDelete($model, 'user', 'Notifications deleted successfully', 'No notifications selected', 'delete_notifications', $request->bd);
        }
        return $notification;
    }

    public function staticBulkDelete($model, $perm_name, $msg, $error_msg, $permission, $id)
    {
        $notification = [];
        if (!$this->$perm_name->can($permission)) abort(403);

        if (!empty($id) > 0) {
            foreach ($id as $key => $value) {
                $delete = $model::where('id', $key)->firstOrFail();
                $delete->delete();
            }
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = $msg;/* "User deleted successfully"; */
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = $error_msg; //"No user selected";
        }
        return redirect()->back()->with('notification', $notification);
    }
    public function permantBulkDelete($model, $perm_name, $msg, $error_msg, $permission, $id)
    {
        $notification = [];
        if (!$this->$perm_name->can($permission)) abort(403);
        if (!empty($id) > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($id as $key => $value) {
                // slide remove
                $slides = Slide::where('slider_id', $key)->delete();

                $delete = Slider::withTrashed()->where('id', $key)->firstOrFail();
                $delete->forceDelete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = $msg;/* "User deleted successfully"; */
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = $error_msg; //"No user selected";
        }
        return redirect()->back()->with('notification', $notification);
    }
}
