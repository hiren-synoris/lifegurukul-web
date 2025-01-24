<?php

namespace App\Http\Controllers\admin;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\admin\ChapterRequest;
use App\Http\Requests\admin\CourseRequest;
use App\Mail\SupportTicketCreatedMail;
use App\Models\Chapter;
use App\Models\ChapterInfo;
use App\Models\CouponUsage;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CoursePackage;
use App\Models\CoursePlan;
use App\Models\DropdownOption;
use App\Models\Instructors;
use App\Models\Learner;
use App\Models\Media;
use App\Models\Notification;
use App\Models\RatingReview;
use App\Models\RenewingSubscriptions;
use App\Models\SellCourseTimer;
use App\Models\Slide;
use App\Models\Support;
use App\Models\User;
use App\Models\UserCoin;
use App\Models\UserCourse;
use App\Models\UserCourseProgress;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Yajra\DataTables\DataTables;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!$this->user->can('browse_courses')) {
            abort(403);
        }

        $pg_header = "Courses";
        $user = Auth::user();
        $users = Instructors::select('id', 'user_id', 'designation', 'instagram_follower', 'facebook_follower', 'twitter_follower', 'youtube_follower', 'bio as about')
            ->with(['user' => function ($query) {
                $query->select('id', 'name');
            }])
            ->get();
        // dd($users);
        // $courses = Course::all();
        //  dd($users);
        if (view()->exists('admin.courses.list')) {
            return view('admin.courses.list', compact('pg_header', 'users', 'user'));
        }
        abort(404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->can('add_courses')) {
            abort(403);
        }
        $pg_header = "Add Course";
        $instructors = Helper::getInstructure();
        //$instructors = Instructors::whereNull('deleted_at')->select('id', 'name')->get();
        $courseCategories = DropdownOption::getDropdownCategories('course_category')
            ->select('id', 'dropdown_id', 'name')
            ->where('status', '1')
            ->get();
        if (view()->exists('admin.courses.add')) {
            return view('admin.courses.add', compact('instructors', 'courseCategories', 'pg_header'));
        }
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CourseRequest $request)
    {

        if (!$this->user->can('add_courses')) {
            abort(403);
        }

        try {
            $notification = [];
            DB::beginTransaction();
            $course = Course::create([
                // 'title' => allowWhiteSpace($request['title']),
                'title' => $request['title'],
                'instructor_id' => $request['instructor_id'],
                'slug' => $request['slug'],
                'course_coin' => $request['course_coin'],
                'course_platform' => '1,2,3',
                'type' => $request['type'],
                'order' => ($request['order']) ?? null,
                'status' => (request()->has('status') && $request['status'] == 'on') ? 1 : 0,
                'created_by' => auth()->id(),
                'is_featured' => (request()->has('featured') && $request['featured'] == 'on') ? 1 : 0,
                'is_free' => (request()->has('is_free') && $request['is_free'] == 'on') ? 1 : 0,
                "completely_watch" => $request->completely_watch,
                "course_finished" => $request->course_finished,
                "course_finished_day" => $request->course_finished_day,
                "days" => $request->days,
            ]);
            // dd($request->category_id);
            $data['lng'] = ($request['lng']) ? $request['lng'] : 1;

            if (request()->has('category_id') && !empty($request->category_id)) {
                Course::findOrFail($course->id)->update($data);
                foreach ($request->category_id as $key => $value) {
                    CourseCategory::updateOrCreate([
                        'course_id' => $course->id,
                        'category_id' => $value,
                    ], [
                        'course_id' => $course->id,
                        'category_id' => $value,
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Course created successfully";
            return redirect()->route('courses.edit', ['course' => $course->id])->with('notification', $notification);
        } catch (\Exception $e) {
            // dd($e->getMessage());
            DB::rollback();
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "Something went wrong.";
        }
        return redirect('backoffice/courses')->with('notification', $notification);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!$this->user->can('read_courses')) {
            abort(403);
        }

        $course = Course::with('user:id,name')->findOrFail($id);
        $created_at = date('d/m/Y H:i:s', strtotime($course->created_at));
        $updated_at = date('d/m/Y H:i:s', strtotime($course->updated_at));
        $pg_header = "View Course";
        return view('admin.courses.view', compact('course', 'created_at', 'updated_at', 'pg_header'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!$this->user->can('edit_courses')) {
            abort(403);
        }

        $instructors = Helper::getInstructure();
        // $instructors = Instructors::whereNull('deleted_at')->get();
        // $course = Course::with('categories')
        //                     ->where('id', $id)
        //                     ->firstOrFail();
        $course = Course::with(['categories' => function ($query) {
            $query->whereNull('course_categories.deleted_at');
        }])->where('id', $id)
            ->firstOrFail();

        $courseCategories = DropdownOption::getDropdownCategories('course_category')
            ->select('id', 'dropdown_id', 'name')
            ->where('status', '1')
            ->get();

        $renewingSubscriptions = RenewingSubscriptions::all();

        return view('admin.courses.edit', compact('course', 'instructors', 'courseCategories', "renewingSubscriptions"));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CourseRequest $request, $id)
    {

// dd($request->all());
        if (!$this->user->can('edit_courses')) {
            abort(403);
        }

        $notification = [];

        $coursePlatform = null;

        if (request()->has('course_platform')) {
            $coursePlatform = implode(',', $request['course_platform']);
            // dd($coursePlatform);
        }
        $course = Course::findOrFail($id);
        $introVideo = $course->intro_video;
        if (request()->hasFile('intro_video')) {
            if (!empty($course->intro_video)) {
                Storage::delete($course->intro_video);
            }
            $file = $request->file('intro_video');
            $filename = time() . '_' . $file->getClientOriginalName();

            // File upload location
            $location = $file->storeAs('profile/introVideo', $filename);

            // Update Image
            $introVideo = $location;
        }

        $bannerImage = $course->banner_image;
        if (request()->hasFile('banner_image')) {
            if (!empty($course->banner_image)) {
                Storage::delete($course->banner_image);
            }
            $file = $request->file('banner_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            // File upload location
            $location = $file->storeAs('profile/bannerImage', $filename);
            // Update Image
            $bannerImage = $location;
        }
        if ($request->input('remove_banner_image') == '1') {
            if ($course && !empty($course->banner_image)) {
                Storage::delete($course->banner_image);
                $course->banner_image = null;
                $course->save();
            }
        }
        if ($request->input('remove_intro_video') == '1') {
            if ($course && !empty($course->intro_video)) {
                Storage::delete($course->intro_video);
                $course->intro_video = null;
                $course->save();
            }
        }

        $tags = null;
        $keywords = null;
        if (request()->has('tags')) {

            $tags = implode(',', $request['tags']);
        }
        if (request()->has('meta_keywords')) {
            $keywords = implode(',', $request['meta_keywords']);
        }
        $data = [];
        if ($request['title']) {
            $data['title'] = $request['title'];
        }
        if ($request['instructor_id']) {
            $data['instructor_id'] = $request['instructor_id'] ? $request['instructor_id'] : null;
        }
        if ($request['instructor_name']) {
            $data['instructor_name'] = $request['instructor_name'] ? $request['instructor_name'] : null;
        }
        // if (request()->has('course_platform')) {
        $data['course_platform'] = $coursePlatform;
        // }
        if (request()->has('order')) {
            $data['order'] = $request['order'];
        }

        $data['status'] = request()->has('status') ? 1 : 0;
        $data['updated_by'] = auth()->id();
        $data['is_featured'] = request()->has('featured') ? 1 : 0;
        $data['is_free'] = request()->has('is_free') ? 1 : 0;
        $data['public_forum_status'] = $request->public_forum_status == "on" ? 1 : 0;

        // $data['is_freely_avil_learner'] = ($request['is_freely_avil_learner'] != null && $request['is_freely_avil_learner'] == 'on')? 1 : 0;
        $data['show_learner_cnt'] = ($request['show_learner_cnt'] != null && $request['show_learner_cnt'] == 'on') ? 1 : 0;
        // $data['show_validity_learner'] = ($request['show_validity_learner'] != null && $request['show_validity_learner'] == 'on')? 1 : 0;
        // $data['allow_offline_data'] = ($request['allow_offline_data'] != null && $request['allow_offline_data'] == 'on') ? 1 : 0;
        $data['allow_bookmark'] = ($request['allow_bookmark'] != null && $request['allow_bookmark'] == 'on') ? 1 : 0;

        if (request()->has('banner_image')) {
            $data['banner_image'] = $bannerImage;
        }
        if (request()->has('intro_video')) {
            $data['intro_video'] = $introVideo;
        }

        $data['description'] = $request['description'] ? $request['description'] : null;
        $data['course_coin'] = $request['course_coin'] ? $request['course_coin'] : null;
        $data['tagline'] = $request['tagline'] ? $request['tagline'] : null;
        $data['how_to_use'] = $request['how_to_use'] ? $request['how_to_use'] : null;
        $data['lng'] = ($request['lng']) ? $request['lng'] : 1;
        $data['tags'] = $tags;
        $data['hours'] = request()->has('hours') ? $request['hours'] : 0;
        $data['minutes'] = request()->has('minutes') ? $request['minutes'] : 0;
        $data["completely_watch"] = $request->completely_watch;
        $data["course_finished"] = $request->course_finished;
        $data["course_finished_day"] = $request->course_finished_day;
        $data["days"] = $request->days;

        // $data['meta_title'] = $request['meta_title'] ?? NULL;
        // $data['meta_description']= $request['meta_description'] ?? NULL;
        // $data['meta_keywords'] = $keywords ?? NULL;
        Course::findOrFail($id)->update($data);

        if (request()->has('category_id') && !empty($request->category_id)) {
            CourseCategory::where('course_id', $id)->whereNotIn('category_id', $request->category_id)->delete();
            foreach ($request->category_id as $key => $value) {
                CourseCategory::updateOrCreate([
                    'course_id' => $id,
                    'category_id' => $value,
                ], [
                    'course_id' => $id,
                    'category_id' => $value,
                    'created_by' => auth()->id(),
                ]);
            }
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Course updated successfully";
        return redirect()->back()->with('notification', $notification);

    }

    /**
     * Course SEO update
     */
    public function seoUpdate(Request $request, $id)
    {

        // $request->validate([
        //     'meta_title' => 'required',
        //     'meta_keywords' => 'required|array',
        //     'meta_description' => 'required',
        // ]);

        if (!$this->user->can('edit_courses')) {
            abort(403);
        }

        $notification = [];
        if (request()->has('meta_keywords')) {
            $meta_keywords = implode(',', $request['meta_keywords']);
        }

        Course::findOrFail($id)->update([
            'meta_title' => request()->has('meta_title') ? $request['meta_title'] : null,
            'meta_keywords' => $meta_keywords ?? null,
            'meta_description' => request()->has('meta_description') ? $request['meta_description'] : null,
            'updated_by' => auth()->id(),
        ]);

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Course updated successfully";
        return redirect()->back()->with('notification', $notification);
    }

    /**
     * Update type
     * 1 = Course, 2 = Package
     */
    public function updateType(Request $request, $id)
    {
        if ($request->ajax()) {
            $course = Course::findOrFail($id);
            $course->type = request()->has('selectedType') ? $request->selectedType : 1;
            $course->save();

            $type = $request->selectedType == 1 ? 'Course.' : 'Package.';
            $text = 'You select ' . $type;

            return response()->json([
                'success' => true,
                'type' => $request->selectedType,
                'message' => 'Record updated successfully',
                'text-message' => $text,
            ], 200);
        }
        return response()->json([
            'success' => false,
            'errors' => 'Something went wrong!',
        ], 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        if (!$this->user->can('delete_courses')) {
            abort(403);
        }

        $notification = [];
        $course = Course::where('id', $id)->firstOrFail();

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // Chapter::where('title', $id)->where('asset_type',7)->delete();

        // ChapterInfo::whereIn('chapter_id', function ($query) use ($id) {
        //     $query->select('id')
        //         ->from('chapters')
        //         ->where('course_id', $id);
        // })->delete();

        // Chapter::where('course_id', $id)->delete();

        $chp = Chapter::where("course_id", $id)->get();
        foreach ($chp as $val) {
            UserCourseProgress::where('chapter_id', $val->id)->delete();
        }

        CoursePlan::where('course_id', $id)->delete();
        CoursePackage::where('course_id', $id)->delete();
        Wishlist::where('course_id', $id)->delete();
        Notification::where('courseId', $id)->delete();
        UserCourse::where('course_id', $id)->delete();
        RatingReview::where('course_id', $id)->delete();
        CourseCategory::where('course_id', $id)->delete();
        UserCoin::where('course_id', $id)->delete();
        $course->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        // if($course->type==2){
        //     $notification['msg'] = "Package deleted successfully";
        // }
        // else{
        $notification['msg'] = "Course deleted successfully";
        // }

        return redirect()->back()->with('notification', $notification);
    }

    /**
     * Restore specified record .
     */
    public function restore($id)
    {

        if (!$this->user->can('restore_courses')) {
            abort(403);
        }

        $notification = [];
        $course = Course::withTrashed()->where('id', $id)->firstOrFail();
        $course->restore();

        CoursePlan::withTrashed()->where('course_id', $id)->restore();
        RatingReview::withTrashed()->where('course_id', $id)->restore();
        CourseCategory::withTrashed()->where('course_id', $id)->restore();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Course restored successfully";

        return redirect()->back()->with('notification', $notification);
    }

    /**
     * Get all Courses record where not deleted.
     */
    public function getCourses(Request $request)
    {
        // dd($request->all());
        if (!$this->user->can('browse_courses')) {
            abort(403);
        }

        $pageSize = (isset($_GET["length"])) ? $_GET["length"] : 10;
        $start = (isset($_GET["start"])) ? $_GET["start"] : 0;

        $query = Course::with(['user:id,name', "coursePlan", "userCourseCount"])

            ->select('id', 'title', 'instructor_id', 'description', 'created_at', 'deleted_at', 'status', 'image', 'order as order_data', 'type', 'slug', 'is_free', 'banner_image', 'approval_status')
            ->whereNull('deleted_at')
            ->when($request->has('user_id') && !empty($request->user_id),
                function ($query) use ($request) {
                    return $query->where('instructor_id', $request->user_id);
                })
            ->when($request->has('status') && $request->status != '',
                function ($query) use ($request) {
                    return $query->where('status', $request->status);
                })
            ->withCount(['coursePlan' => function ($query) use ($request) {
                $query->where('status', 1);
                $query->where('created_at', '<', Carbon::now());

            }])
            ->with(['userCourseCount' => function ($query) {
                $query->selectRaw('course_id, COUNT(DISTINCT learner_id) as user_course_count')
                    ->groupBy('course_id');
            }]);

        if ($request->enroll_course != true) {
            $query = $query->where('type', Course::COURSE);
        }
        //    $query  = $query ->orderBy('id', 'desc');
        $query = $query;

        if (auth()->user()->hasRole(User::INSTRUCTOR)) {
            $query = $query->where('instructor_id', auth()->id());
        } else {
            $query = $query;
        }

        if (isset($_GET["search"]["value"]) && !empty($_GET["search"]["value"])) {
            $searchValue = $_GET["search"]["value"];

            $dateTime = \DateTime::createFromFormat('d/m/Y H:i:s', $searchValue);

            if ($dateTime) {
                $formattedDate = $dateTime->format('Y-m-d H:i:s');

            } else {
                $formattedDate = null;
            }

            $query->where(function ($query) use ($searchValue, $formattedDate) {
                $query->whereHas('user', function ($q) use ($searchValue) {
                    $q->where('users.name', 'like', "%$searchValue%");
                })
                    ->orWhere('title', 'like', "%$searchValue%")
                    ->orWhere('id', 'like', "%$searchValue%");
                if ($formattedDate) {
                    $query->orWhere('created_at', 'like', "%$formattedDate%");
                }
            });
        }

        $orderColumn = isset($_GET['order'][0]['column']) ? $_GET['order'][0]['column'] : null;
        $orderDir = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'desc';

        $columns = [
            2 => 'title',
            3 => 'instructor_id',
            8 => 'created_at',
            6 => 'user_course_count',
            7 => 'status',
        ];

        if ($orderColumn !== null && isset($columns[$orderColumn])) {
            if ($columns[$orderColumn] == 'user_course_count') {
                $query->withCount('userCourseCount as user_course_count_sum');
                $query->orderBy('user_course_count_sum', $orderDir);
            } else {

                $query->orderBy($columns[$orderColumn], $orderDir);
            }
        } else {

            $query->orderBy('id', 'desc');
        }

        $pageSize = (isset($_GET["length"])) ? $_GET["length"] : 10;
        $start = (isset($_GET["start"])) ? $_GET["start"] : 0;

        $count_record = $query->count();
        // dd($query->get()->toArray(),$count_record);
        $data = $query->skip($start)->take($pageSize);
        // dd($data->get());

        return DataTables::of($data)->with([
            "recordsTotal" => $count_record,
            "recordsFiltered" => $count_record,
        ])
            ->addColumn('action', function ($row) use ($request) {
                // dd("");
                $url = route("courses.destroy", ["course" => $row->id]);
                $button = "";
                $disabled = "";
                if ($row->approval_status == 1) {
                    $disabled = " disabled_cls";
                }

                if ($this->user->can('edit_courses')) {
                    if (auth()->user()->hasRole(User::INSTRUCTOR) && $row->status !== 1) {
                        $button .= '<a class="mx-1 ' . $disabled . ' " title="Ask for Approval" href="' . route('ask.approval', ['course_id' => $row->id]) . '"><i class="fas fa-check-circle text-green"></i></a>';
                    }
                    $button .= '<a class="mx-1" title="Edit" href="' . url('backoffice/courses/' . $row->id . '/edit') . '"><i class="fas fa-edit text-orange"></i></a>';
                    // if($row->type == 1){
                    $button .= '<a class="mx-1" title="Course Builder" href="' . url('backoffice/courses/' . $row->id . '/builder') . '"><i class="fas fa-tools text-olive"></i></a>';

                    $button .= '<a class="mx-1" target="_blank" title="Course Preview" href="' . url('course-view/' . $row->slug) . '"><i class="fas fa-film text-info"></i></a>';

                    $button .= '<a class="mx-1" target="_blank" title="Landing Page" href="' . url('course-details/' . $row->slug) . '"><i class="fas fa-eye text-orange"></i></a>';
                    // }else{
                    //     $button .= '<a class="mx-1" title="Package Builder" href="' . url('backoffice/courses/' . $row->id . '/package') . '"><i class="fas fa-tools text-red"></i></a>';

                    //     $button .= '<a class="mx-1" target="_blank" title="Landing Page" href="' . url('course-package/' . $row->slug) . '"><i class="fas fa-eye  text-orange"></i></a>';
                    // }

                    $button .= '<a class="mx-1" title="Learners" href="' . url('backoffice/courses/' . $row->id . '/learners') . '"><i class="fas fa-user-friends text-purple"></i></a>';

                    // $button .= '<a class="mx-1" title="Course Preview" href="javascript:void(0)"><i class="fas fa-eye text-orange"></i></a>';
                }

                // if ($this->user->can('read_courses')) {
                //     $button .= '<a class="mx-1" title="View" href="' . url('backoffice/courses/' . $row->id) . '"><i class="fas fa-eye"></i></a>';
                // }
                // if ($this->user->can('edit_courses')) {
                //     $button .= '<a class="mx-1" title="Edit" href="' . url('backoffice/courses/' . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                // }
                if (is_null($row->deleted_at)) {
                    if ($this->user->can('delete_courses')) {
                        $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                    }
                } else {
                    if ($this->user->can('restore_courses')) {
                        $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/courses/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                }
                if ($request->enroll_course == true) {
                    // return "<a class='mx-1 text-success learner_course_'  data-toggle='modal' data-target='#enroll_course_plan'  href='javascript:void(0)' title='Add' data-learnerid='$request->learner_id' data-course_id='$row->id'><i class='fas fa-plus-square'></i></a>";
                    return "<a class='mx-1 text-success learner_course_'  href='javascript:void(0)' title='Add' data-learnerid='$request->learner_id' data-course_id='$row->id'><i class='fas fa-plus-square'></i></a>";
                }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->addColumn('image', function ($row) {
                $pictureURL = getImageIfExists($row->image, course_img_default());
                return '<a href="' . $pictureURL . '" target="_blank"><img src="' . $pictureURL . '" style="height: 60px;width: 60px;"></a>';
            })
            ->addColumn('title', function ($row) {
                if ($row->type == 2) {

                    return isset($row->title) && !empty($row->title) ? '<a href="' . route('packages.edit', ['package' => $row->id]) . '">' . $row->title . '</a>' : '';
                } else {

                    return isset($row->title) && !empty($row->title) ? '<a href="' . route('courses.edit', ['course' => $row->id]) . '">' . $row->title . '</a>' : '';
                }
            })
            ->addColumn('instructor_id', function ($row) {
                return isset($row->user) && !empty($row->user) ? $row->user->name : '';
            })
            ->addColumn('status', function ($row) {
                return Helper::checkPublish($row->status);
            })
            ->addColumn('type', function ($row) {
                return Helper::checkType($row->type);
            })
            ->addColumn('created_at', function ($row) {
                // dd($row);
                return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
            })

            ->addColumn('course_plan_count', function ($row) {

                $url = url("backoffice/courses/$row->id/edit");

                return '<a href="' . $url . '">' . $row->course_plan_count . '</a>';
            })
            ->addColumn('user_course_count', function ($row) {
                $url = url("backoffice/courses/$row->id/learners");

                // $course_package = CoursePackage::where("course_id", $row->id)
                //     ->with(['getPackageBasedCourse' => function ($query) {
                //         $query->selectRaw('course_id, COUNT(DISTINCT learner_id) as package_count')
                //             ->groupBy('course_id');
                //     }])->whereHas("getPackageBasedCourse")
                //     ->first();
                // $pck_count = $course_package
                //     ? $course_package->getPackageBasedCourse->sum('package_count')
                //     : 0;

                // $user_course_count = collect($row->userCourseCount)->sum('user_course_count');

                // return $user_course_count + $pck_count;

                // $course_package = CoursePackage::where("course_id", $row->id)->whereHas("getPackageBasedCourse")->first();

                // $userCourse = UserCourse::where("course_id", $row->id)->Orwhere("course_id", @$course_package->package_id)->get()

                //     ->unique('learner_id', 'course_id');

                // return $userCourse->count();

                $course_packages = CoursePackage::where("course_id", $row->id)
                    ->whereHas("getPackageBasedCourse")->Orwhere("course_id", @$course_package->package_id)
                    ->pluck('package_id');
                $course_ids = $course_packages->push($row->id);

                $sum = UserCourse::whereIn("course_id", $course_ids)
                    ->get()
                    ->unique('learner_id', 'course_id')->count();

                return $sum;

            })

            ->rawColumns(['type', 'image', 'action', 'status', 'title', 'created_at', "course_plan_count", "user_course_count", "instructor_id"])
            ->addIndexColumn()
            ->toJson();
    }
    // public function getCourses(Request $request)
    // {
    //     // dd($request->all());
    //     if (!$this->user->can('browse_courses')) {
    //         abort(403);
    //     }

    //     $pageSize = (isset($_GET["length"])) ? $_GET["length"] : 10;
    //     $start = (isset($_GET["start"])) ? $_GET["start"] : 0;

    //     $query = Course::select("*");

    //     $pageSize = (isset($_GET["length"])) ? $_GET["length"] : 10;
    //     $start = (isset($_GET["start"])) ? $_GET["start"] : 0;

    //     $count_record = $query->count();
    //     $data = $query->skip($start)->take($pageSize);

    //     return DataTables::of($data)->with([
    //         "recordsTotal" => $count_record,
    //         "recordsFiltered" => $count_record,
    //     ])

    //         ->addColumn('user_course_count', function ($row) {

    //             $course_packages = CoursePackage::where("course_id", $row->id)
    //                 ->whereHas("getPackageBasedCourse")->Orwhere("course_id", @$course_package->package_id)
    //                 ->pluck('package_id');
    //             $course_ids = $course_packages->push($row->id);

    //             $sum = UserCourse::whereIn("course_id", $course_ids)
    //                 ->get()
    //                 ->unique('learner_id', 'course_id')->count();

    //             return $sum;

    //         })

    //         ->rawColumns(["user_course_count"])
    //         ->addIndexColumn()
    //         ->toJson();
    // }

    public function askforApproval(Request $request)
    {

        if (!$this->user->can('add_support')) {
            abort(403);
        }

        $notification = [];
        // $validated = $request->validate([
        //     // 'subject' => 'required|filled|regex:/^[a-zA-ZÑñ\s]+$/|max:255',
        //     'subject' => 'required|filled',
        //     'description'=>['required','filled'],
        // ]);
        $users = User::role('admin')->select(['id', 'name', 'email'])->get();
        $course = Course::findOrFail($request->course_id);

        if (!empty($course)) {
            Course::where('id', $request->course_id)->update(['approval_status' => "1"]);
        }

        $subject = "Support ticket mail send";
        $instructor = User::select(['id', 'name', 'email'])->where('id', auth()->id())->firstOrFail();
        $support = Support::create([
            'subject' => allowWhiteSpace($instructor->name . ' has aksed for ' . $course->title . ' approval'),
            'description' => 'Please check course and approve or reply from support ticket in admin',
            'created_by' => auth()->id(),
        ]);

        $url = route('support-ticket.index');
        if (isset($users) && !empty($users)) {
            foreach ($users as $user) {
                Mail::to($user->email)->send(new SupportTicketCreatedMail($user, $instructor, $support, $url, $subject));
            }
        }

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Sent request for approval successfully";
        return redirect('backoffice/courses')->with('notification', $notification);
    }

    /**
     * Get deleted Courses records.
     */
    public function getCoursesDeleted(Request $request)
    {
        if (!$this->user->can('browse_courses')) {
            abort(403);
        }

        $query = Course::with(['user:id,name', 'instructor'])
            ->select('id', 'title', 'instructor_id', 'description', 'status', 'created_at', 'deleted_at', 'status', 'image', 'order as order_data', 'type', 'slug', 'is_free')
            ->where('type', Course::COURSE)
            ->onlyTrashed()
            ->withCount(['coursePlan' => function ($query) {
                $query->where('status', 1);
                $query->where('created_at', '<', Carbon::now());
            }])
            ->when($request->has('user_id') && !empty($request->user_id),
                function ($query) use ($request) {
                    return $query->where('instructor_id', $request->user_id);
                })
            ->when($request->has('status') && $request->status != '',
                function ($query) use ($request) {
                    return $query->where('status', $request->status);
                })
            ->latest();

        if (auth()->user()->hasRole(User::INSTRUCTOR)) {
            $query = $query->where('instructor_id', auth()->id())->get();
        } else {
            $query = $query->get();
        }

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("courses.destroy", ["course" => $row->id]);
                $button = "";
                $deleteurl = route("courses.delete", ["id" => $row->id]);

                if ($this->user->can('delete_courses')) {
                    $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=permanent_delete_confirmation("' . $deleteurl . '")><i class="fas fa-trash-alt"></i></a>';
                }
                if ($this->user->can('restore_courses')) {
                    $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/courses/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                }
                // if (is_null($row->deleted_at)) {
                //     if ($this->user->can('delete_courses')) {
                //         $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                //     }
                // } else {
                //     if ($this->user->can('restore_courses')) {
                //         $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/courses/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                //     }
                // }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('image', function ($row) {
                $pictureURL = getImageIfExists($row->image);
                return '<a href="' . $pictureURL . '" target="_blank"><img src="' . $pictureURL . '" style="height: 60px;width: 60px;"></a>';
            })
            ->editColumn('course_plan_count', function ($row) {

                $url = url("backoffice/courses/$row->id/edit");

                return '<a href="' . $url . '">' . $row->course_plan_count . '</a>';
            })

            ->editColumn('user_course_count', function ($row) {
                return 0;
            })
            ->editColumn('title', function ($row) {
                return isset($row->title) && !empty($row->title) ? '<a href="' . route('courses.edit', ['course' => $row->id]) . '">' . $row->title . '</a>' : '';
            })
        // ->editColumn('instructor_id', function ($row) {
        //     $instructure = Helper::getInstructure($row->instructor_id);
        //     return $instructure?$instructure->name:"";
        // })
            ->editColumn('instructor_id', function ($row) {
                return isset($row->instructor) && !empty($row->instructor) ? $row->instructor->name : '';
            })
            ->editColumn('status', function ($row) {
                return Helper::checkPublish($row->status);
            })
            ->editColumn('type', function ($row) {
                return Helper::checkType($row->type);
            })
            ->editColumn('created_at', function ($row) {
                return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['type', 'image', 'action', 'status', 'title', 'created_at', "course_plan_count", "user_course_count"])
            ->addIndexColumn()
            ->toJson();
    }

    /**
     * Restore all Courses records.
     */
    public function restore_all(Request $request)
    {
        if (!$this->user->can('restore_courses')) {
            abort(403);
        }

        foreach (array_keys($request->selected_checkbox) as $key => $id) {
            $course = Course::withTrashed()->where('id', $id)->firstOrFail();
            $course->restore();

            CoursePlan::withTrashed()->where('course_id', $id)->restore();
            RatingReview::withTrashed()->where('course_id', $id)->restore();
            CourseCategory::withTrashed()->where('course_id', $id)->restore();

        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Courses restored successfully";
        return redirect()->back()->with('notification', $notification);
    }

    public function courseLearners(Request $request, $id)
    {
        // if (!$this->user->can('edit_courses')) {
        //     abort(403);
        // }
        // // dd("");

        // try {
        if (view()->exists('admin.courses.learners.index')) {
            $course = Course::withTrashed()
                ->where('id', $id)
                ->with(['plans' => function ($query) {
                    $query->where(function ($q) {
                        $q->where('is_fixed_date', '!=', 1)
                            ->orWhere('access_value', '>', Carbon::now());
                    })->where('status', 1);
                }])
                ->first();

            return view('admin.courses.learners.index', [
                'courseId' => $id,
                'course' => $course,
            ]);
        }

        //     abort(404);
        // } catch (\Exception $e) {
        //     return abort(404);
        // }
    }
/**
 * Reset Course with Specific learner
 */
    public function resetLearner($courseId, $learnerId)
    {

        $chapterId = Chapter::where("course_id", $courseId)->selectRaw('group_concat(id) as ids')->pluck('ids')->first();

        SellCourseTimer::where("Learner_id", $learnerId)->where("buy_sell_course_id", $courseId)->delete();

        if (!empty($chapterId)) {
            $chapterIds = explode(",", $chapterId);
            $delete = UserCourseProgress::where('learner_id', $learnerId)->whereIn('chapter_id', $chapterIds)->delete();

        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Reset successfully";

        return response()->json("Reset successfully");
        // return redirect()->back()->with('notification', $notification);

    }
    /**
     * Get all Learners inside Course module edit page.
     * @return DataTable query AJAX call.
     */
    public function get_learners($courseId)
    {

        if (!$this->user->can('edit_courses')) {
            abort(403);
        }

        // $course_package = CoursePackage::where("course_id", $courseId)->whereHas("getPackageBasedCourse")->first();
        // dd($courseId);

        $course_packages = CoursePackage::where("course_id", $courseId)
            ->whereHas("getPackageBasedCourse")->Orwhere("course_id", @$course_package->package_id)
            ->pluck('package_id');
        $course_ids = $course_packages->push($courseId);

        $package_course_id = @$course_package->course_id;

        $query = UserCourse::with(['learner', 'course', 'userChpater', "countryName"])

            ->where('course_id', $courseId)
            ->orwhereIn("course_id", $course_ids)
            ->orderBy('id', 'desc')
            ->with(["userChpater" => function ($q) {
                $q->select('course_id', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id');
            }])
            ->get()
            ->unique('learner_id', 'course_id');

        return DataTables::of($query)
            ->addColumn('action', function ($row) use ($package_course_id) {
                $url = route("courses.learners.delete", ["id" => $row->id]);
                $button = "";
                if ($this->user->can('delete_courses')) {
                    $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                }
                // reset learner by Course
                if (@$row->course->type == 1) {
                    $resetUrl = route("reset.learner", ["courseId" => $row->course_id, "learnerId" => $row->learner_id]);
                    $button .= '<a class="mx-1 text-primary" title="Reset" type="button" href="' . $resetUrl . '"><i style="font-size:15px" class="fa">&#xf021;</i>
                    </a>';
                }
                // else {

                //     $resetUrl = route("reset.learner", ["courseId" => $package_course_id, "learnerId" => $row->learner_id]);
                //     $button .= '<a class="mx-1 text-primary" title="Reset" type="button" href="' . $resetUrl . '"><i style="font-size:15px" class="fa">&#xf021;</i>
                //     </a>';

                // }

                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('name', function ($row) {
                // return (!empty($row->learner->name) && isset($row->learner->name)) ? $row->learner->name : '';
                return $row->learner->name ?? '';
            })
        // ->editColumn('mobile', function ($row) {
        //     // return (!empty($row->learner->name) && isset($row->learner->name)) ? $row->learner->name : '';
        //     return $row->learner->mobile ?? '';
        // })
            ->editColumn('mobile', function ($row) {
                // $country_code = Countries::where("id", @$row->learner->country_id)->first();

                return @"+" . @$row->countryName->phonecode . " " . @$row->learner->mobile ?? '';

            })
            ->editColumn('email', function ($row) {
                // return (!empty($row->learner->email) && isset($row->learner->email)) ? $row->learner->email : '';
                return $row->learner->email ?? '';
            })
            ->editColumn('created_at', function ($row) {
                return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
            })
            ->editColumn('expire_at', function ($row) {
                return !empty($row->expire_at) ? created_at_hidden($row->expire_at) . ' ' . Helper::only_date_format($row->expire_at) : 'LifeTime';
            })
            ->editColumn('order_status', function ($row) {
                // return Helper::checkTransactionType($row->order_status);
                if ($row->order_status == 1) {
                    return '<span class="badge badge-success">completed</span>';
                } else if ($row->order_status == 2) {
                    return '<span class="badge badge-danger">Failure</span>';
                } else if ($row->order_status == 3) {
                    return '<span class="badge badge-success">Free</span>';
                } else if ($row->order_status == 4) {
                    return '<span class="badge badge-success">Enrol by Admin</span>';
                } else if ($row->order_status == 5) {
                    return '<span class="badge badge-success">Zapier</span>';
                }
            })
            ->addColumn('course_progress', function ($row) use ($courseId) {
                if ($row->course->type == 2) {

                    $course = Course::where("id", $courseId)->first();
                    $chapter = Chapter::where("parent_id", "!=", 0)->where("course_id", $course->id)->where("asset_type", "!=", 4)->where("asset_type", "!=", 7)->pluck("id")->toArray();

                    $user_progress = UserCourseProgress::whereIn("chapter_id", $chapter)->where("learner_id", $row->learner_id)->where("is_completed", 1)->count();

                    return round($user_progress) != 0 ? round(($user_progress * 100) / count($chapter)) . "%" : "0%";

                } else {
                    $row->userChpater->filter(function ($fl) use ($row) {
                        return $row->new_chapterIds = $fl->chapterIds;
                    });
                    $user_progress = UserCourseProgress::whereIn("chapter_id", explode(",", $row->new_chapterIds))->where("learner_id", $row->learner_id)->where("is_completed", 1)->count();

                    return round($user_progress) != 0 ? round(($user_progress * 100) / count(explode(",", $row->new_chapterIds))) . "%" : "0%";
                }

            })
            ->rawColumns(['name', 'email', 'created_at', 'expire_at', 'order_status', 'action', 'course_progress'])
            ->addIndexColumn()
            ->toJson();
    }
    public function getLearnersPackage($courseId)
    {

        if (!$this->user->can('edit_courses')) {
            abort(403);
        }

        $query = UserCourse::with(['learner', 'course'])
            ->where('course_id', $courseId)
            ->orderBy('id', 'desc')
            ->get()
            ->unique('learner_id', 'course_id');

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("courses.learners.delete", ["id" => $row->id]);
                $button = "";
                if ($this->user->can('delete_courses')) {
                    $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                }
                // reset learner by Course
                $resetUrl = route("reset.learner", ["courseId" => $row->course_id, "learnerId" => $row->learner_id]);

                if (@$row->course->type == 1) {
                    $button .= '<a class="mx-1 text-primary" title="Reset" type="button" href="' . $resetUrl . '"><i style="font-size:15px" class="fa">&#xf021;</i>
                    </a>';
                }
                // else {

                //     $resetUrl = route("reset.learner", ["courseId" => $package_course_id, "learnerId" => $row->learner_id]);
                //     $button .= '<a class="mx-1 text-primary" title="Reset" type="button" href="' . $resetUrl . '"><i style="font-size:15px" class="fa">&#xf021;</i>
                //     </a>';

                // }

                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('name', function ($row) {
                return $row->learner->name ?? '';
            })

            ->editColumn('mobile', function ($row) {
                // $country_code = Countries::where("id", @$row->learner->country_id)->first();

                return "+" . @$row->countryName->phonecode . " " . @$row->learner->mobile ?? '';

            })
            ->editColumn('email', function ($row) {
                // return (!empty($row->learner->email) && isset($row->learner->email)) ? $row->learner->email : '';
                return $row->learner->email ?? '';
            })
            ->editColumn('created_at', function ($row) {
                return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
            })
            ->editColumn('expire_at', function ($row) {
                return !empty($row->expire_at) ? created_at_hidden($row->expire_at) . ' ' . Helper::only_date_format($row->expire_at) : 'LifeTime';
            })
            ->editColumn('order_status', function ($row) {
                if ($row->order_status == 1) {
                    return '<span class="badge badge-success">completed</span>';
                } else if ($row->order_status == 2) {
                    return '<span class="badge badge-danger">Failure</span>';
                } else if ($row->order_status == 3) {
                    return '<span class="badge badge-success">Free</span>';
                } else if ($row->order_status == 4) {
                    return '<span class="badge badge-success">Enrol by Admin</span>';
                } else if ($row->order_status == 5) {
                    return '<span class="badge badge-success">Zapier</span>';
                }
            })

            ->rawColumns(['name', 'email', 'created_at', 'expire_at', 'order_status', 'action'])
            ->addIndexColumn()
            ->toJson();
    }

    public function get_addable_learners($courseId)
    {

        if (!$this->user->can('add_learners')) {
            abort(403);
        }

        $course = Course::where('id', $courseId)->with(['plans' => function ($query) {
            return $query->where('status', 1);
        }])->first();
        // $query = UserCourse::select('learner_id')->distinct()->where('course_id', $courseId)->orWhereDate('expire_at', '<', Carbon::now())->get();
        // dd($query);

        // $query = Learner::distinct()->with(['user_courses.course.plans'])->whereNotIn('id', function ($query) use ($courseId) {
        //     $query->select('learner_id')->distinct()->from('user_courses')->where('course_id', $courseId)
        //     ->whereNull('expire_at')
        //     ->orwhereDate('expire_at', '>', Carbon::now());
        //     // $query->UserCourse::select('learner_id')->distinct()->where('course_id', $courseId)->orWhereDate('expire_at', '<', Carbon::now());
        // })->get();

        $pageSize = (isset($_GET["length"])) ? $_GET["length"] : 10;
        $start = (isset($_GET["start"])) ? $_GET["start"] : 0;

        $query = Learner::distinct();
        //->with(['user_courses.course.plans']);
        // $query->UserCourse::select('learner_id')->distinct()->where('course_id', $courseId)->orWhereDate('expire_at', '<', Carbon::now());
        // ->get();

        if (isset($_GET["search"]["value"]) && !empty($_GET["search"]["value"])) {
            $searchValue = $_GET["search"]["value"];
            $query->where(function ($q) use ($searchValue) {
                $q->where('learners.name', 'like', "%$searchValue%")
                    ->orWhere('learners.email', 'like', "%$searchValue%")
                    ->orWhere('learners.mobile', 'like', "%$searchValue%");
            });
        }

        $count_record = $query->count();
        $data = $query->skip($start)->take($pageSize);
        // dd("");
        return DataTables::of($data)->with([
            "recordsTotal" => $count_record,
            "recordsFiltered" => $count_record,
        ])
        // return DataTables::of($query)
            ->addColumn('action', function ($row) use ($course) {
                // dd($course);
                $url = route("delete_addable.learners", ["id" => $row->id]);
                $button = "";
                if ($this->user->can('add_learners')) {
                    $addurl = route('addable.learners', ['courseId' => $course->id, 'learnerId' => $row->id]);
                    $free = $course->is_free;
                    $show_plan = '';
                    if ($free) {
                        $href = $addurl;
                    } else {
                        $href = "javascript:void(0)";
                    }
                    $show_plan = "show_plan(event,'" . $course->id . "','" . $row->id . "','true')";
                    // if( Auth::user()->hasPermissionTo('add_enroll') ) {
                    $button .= '<a class="mx-1 text-success" href="' . $href . '" title="Add" data-url="' . $addurl . '" data-courseId="' . $course->id . '" data-learnerId="' . $row->id . '" onclick="' . $show_plan . '"><i class="fas fa-plus-square"></i></a>';
                    // }
                }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
        // ->editColumn('created_at', function ($row) {
        //     return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
        // })
            ->editColumn('created_at', function ($row) {
                return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['created_at', 'action'])
            ->addIndexColumn()
            ->toJson();
    }

    /**
     * Remove learner to course
     */
    public function learner_delete(Request $request, $id)
    {

        if (!empty($id)) {
            $user_course = UserCourse::where("id", $id)->first();
            if ($user_course) {
                CouponUsage::where("course_id", $user_course->course_id)->where("learner_id", $user_course->learner_id)->delete();
                UserCoin::where("course_id", $user_course->course_id)->where("learner_id", $user_course->learner_id)->delete();
                $duplicate_learner = UserCourse::where("learner_id", $user_course->learner_id)->where("course_id", $user_course->course_id)->delete();
                $user_course->delete();
            }
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Learner Unenrolled";
        return redirect()->back()->with('notification', $notification);
    }

    public function learnerCourseDelete(Request $request, $id)
    {

        if (!empty($id)) {
            // UserCourse::where("id", $id)->delete();
            $user_course = UserCourse::where("learner_id", $id)->where("course_id",$request->course_id);

            if ($user_course) {
                CouponUsage::where("course_id", $request->course_id)->where("learner_id", $id)->delete();
                UserCoin::where("course_id", $request->course_id)->where("learner_id", $id)->delete();

                $user_course->delete();
                // dD($user_course);
            }
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Unenrolled successfully";
        return response()->json("Unenrolled successfully");
    }
    /**
     * Course > Course builder section
     * @return View OR 404
     */
    public function courseBuilder(Request $request, $id, $chapterId = null)
    {

        ;
        // $validated = $request->validate([

        //     'profile_picture' => 'mimes:jpeg,png,jpg|max:500|dimensions:min_width=1366,max_width=1920,min_height=300,max_height=350'
        // ]);

        // $chapterData = collect([]);
        $chapter = [];
        if (!$this->user->can('edit_courses')) {

            abort(403);
        }

        $course = Course::findOrFail($id);
        // dd($course);

        $coursesCollection = Course::whereNull('deleted_at')
            ->where('title', '!=', '')
        // ->where('status', '1')
            ->where('type', '1')
            ->whereNotNull('title')
            ->orderBy("id", "desc")
            ->get(['id', 'title']);
        $allCourses = [];

        if (isset($coursesCollection) && !empty($coursesCollection) && count($coursesCollection) > 0) {
            foreach ($coursesCollection as $key => $value) {
                $allCourses[$key]['text'] = allowWhiteSpace($value->title);
                $allCourses[$key]['value'] = $value->id;

            }
        }
        $assetTypes = [
            [
                'text' => 'Video',
                'value' => 0,
            ],
            [
                'text' => 'Audio',
                'value' => 1,
            ],
            [
                'text' => 'Image',
                'value' => 8,
            ],
            [
                'text' => 'PDF',
                'value' => 2,
            ],
            [
                'text' => 'File',
                'value' => 3,
            ],
        ];
        $courseChapters = Chapter::getCourseChapters($course->id)->get();
        $courseChapters->map(function ($value, $key) {

            $value->childrens = Chapter::where('parent_id', $value->id)->whereNull('deleted_at')->orderBy('order', 'asc')->get();

        });

        $chapterData = null;
        if ($chapterId != null) {

            $chapterData = ChapterInfo::where('chapter_id', $chapterId)->whereNull('deleted_at')->first();

            $chapter = Chapter::with('media:id,path,videoId')->where('id', $chapterId)->whereNull('deleted_at')->first();
        }

        try {
            $url = explode('builder', url()->current())[1];

            if (empty($url)) {
                $chapter = $courseChapters->first();
                if ($chapter) {
                    return redirect('/backoffice/courses/' . $chapter->course_id . '/builder/' . $chapter->id)->with([
                        'course' => $course,
                        'allCourses' => $allCourses,
                        'assetTypes' => $assetTypes,
                        'courseChapters' => $courseChapters,
                        'chapterData' => $chapterData,
                        'chapter' => $chapter,
                        'chapterId' => $chapterId,
                    ]);
                } else {
                    if (view()->exists('admin.courses.builder.index')) {
                        return view('admin.courses.builder.index', [
                            'course' => $course,
                            'allCourses' => $allCourses,
                            'assetTypes' => $assetTypes,
                            'courseChapters' => $courseChapters,
                            'chapterData' => $chapterData,
                            'chapter' => $chapter,
                            'chapterId' => $chapterId,
                        ]);
                    }
                }

            } else {

                if (view()->exists('admin.courses.builder.index')) {
                    return view('admin.courses.builder.index', [
                        'course' => $course,
                        'allCourses' => $allCourses,
                        'assetTypes' => $assetTypes,
                        'courseChapters' => $courseChapters,
                        'chapterData' => $chapterData,
                        'chapter' => $chapter,
                        'chapterId' => $chapterId,
                    ]);
                }
            }
        } catch (\Exception $e) {

            return abort(404);
        }
    }

    /**
     * Package builder code
     *
     */
    public function coursePackage(Request $request, $id)
    {
        $course = [];
        if (!$this->user->can('edit_courses')) {
            abort(403);
        }

        $courseData = Course::where('id', $id)
            ->firstOrFail();
        // ALL COURSE DATA
        $course = Course::where('type', Course::COURSE)
            ->first();
        try {
            if (view()->exists('admin.courses.packages.index')) {
                return view('admin.courses.packages.index', [
                    'course' => $course,
                    'courseData' => $courseData,
                ]);
            }abort(404);
        } catch (\Exception $e) {
            return abort(404);
        }

    }

    /**
     * Course Package listing where type = 1(course)
     * @return DataTable response
     */
    public function getCoursesForPackages(Request $request, $id)
    {

        if (!$this->user->can('edit_courses')) {
            abort(403);
        }

        $course = Course::findOrFail($id);
        $query = Course::with(['instructor:id,name', 'categories:id,name'])
            ->where('type', Course::COURSE)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->select('id', 'title', 'image', 'instructor_id', 'status', 'created_at', 'deleted_at')
            ->latest()
            ->get();
        // $query->map(function($value){
        //     $value->date = Helper::date_format($value->created_at);
        // });

        return DataTables::of($query)
            ->addColumn('action', function ($row) use ($course) {
                // $url = route("courses.destroy", ["course" => $row->id]);
                $url = route("courses.create");
                $button = "";
                $res = CoursePackage::where("package_id", $course->id)
                    ->where('course_id', $row->id)->first();
                // delete icon
                if ($res) {
                    if ($this->user->can('delete_courses')) {
                        $addurl = route('delete.course.package', ['course_id' => $course->id, 'package_id' => $row->id]);
                        $button .= '<a class="mx-1 text-danger " title="Add" href="' . $addurl . '"><i class="fas fa-trash-alt"></i></a>';
                    }
                    return "<div class='d-flex justify-content-center'>$button</div>";
                }
                // Add Icon
                else {
                    if ($this->user->can('edit_courses')) {
                        $addurl = route('add.course.package', ['course_id' => $course->id, 'package_id' => $row->id]);
                        $button .= '<a class="mx-1 text-success " title="Add" href="' . $addurl . '"><i class="fas fa-plus-square"></i></a>';
                    }
                    return "<div class='d-flex justify-content-center'>$button</div>";
                }

            })
            ->editColumn('image', function ($row) {
                $pictureURL = getImageIfExists($row->image);
                return '<a href="' . $pictureURL . '" target="_blank"><img src="' . $pictureURL . '" style="height: 60px;width: 60px;"></a>';
            })
            ->editColumn('instructor_name', function ($row) {
                return isset($row->instructor) && !empty($row->instructor) ? $row->instructor->name : '';
            })
        // ->editColumn('course_category', function($row){

        //     return Helper::getCategoryName($row->course_category);
        // })
            ->editColumn('status', function ($row) {
                return Helper::checkStatus($row->status);
            })
            ->editColumn('date', function ($row) {
                return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action', 'image', 'status', 'date'])
            ->addIndexColumn()
            ->toJson();
    }

    /**
     * Add course to package
     */
    public function addCourseToPackage(Request $request, $course_id, $package_id)
    {
        if (!empty($package_id) && !empty($course_id)) {
            CoursePackage::updateOrCreate([
                'package_id' => $course_id,
                'course_id' => $package_id,
            ], [
                'package_id' => $course_id,
                'course_id' => $package_id,
                'created_by' => auth()->id(),
            ]);
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Package added successfully";
        return redirect()->back()->with('notification', $notification);
    }
    /**
     * Remove course to package
     */
    public function removeCourseToPackage(Request $request, $package_id, $course_id)
    {
        if (!empty($package_id) && !empty($course_id)) {
            $res = CoursePackage::where("package_id", $package_id)
                ->where('course_id', $course_id)->first();
            if ($res) {
                CoursePackage::where('id', $res->id)->delete();
            }
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Package deleted successfully";
        return redirect()->back()->with('notification', $notification);
    }
    /**
     * Add Multiple course to package
     */
    public function addMultipleCourseToPackage(Request $request, $course_id)
    {
        if (request()->has('bd') && !empty($course_id)) {
            foreach ($request->bd as $key => $value) {
                CoursePackage::updateOrCreate([
                    'package_id' => $course_id,
                    'course_id' => $key,
                ], [
                    'package_id' => $course_id,
                    'course_id' => $key,
                    'created_by' => auth()->id(),
                ]);
            }
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Package added successfully";
        return redirect()->back()->with('notification', $notification);
    }
    /**
     * Multiple course to package from Package
     */
    public function deleteMultipleCourseToPackage(Request $request, $package_id)
    {
        if (!empty($request->bd) > 0) {
            foreach ($request->bd as $course_id => $value) {
                $res = CoursePackage::where("package_id", $package_id)
                    ->where('course_id', $course_id)->first();
                if ($res) {
                    CoursePackage::where('id', $res->id)->delete();
                }
            }
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Package deleted successfully";
            return redirect()->back()->with('notification', $notification);
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "Package not selected";
            return redirect()->back()->with('notification', $notification);
        }
    }

    /* Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function courseBuilderUpdate(ChapterRequest $request, $id, $chapterId = null)
    {

        // dd($request->all());

        if (!$this->user->can('edit_courses')) {
            abort(403);
        }

        $save = 0;
        $msg = '';
        switch ($request->input('action')) {
            case 'save':
                $save = 1;
                break;
            case 'publish':
                $save = 0;
                break;
        }

        // publish course
        // if($save == 0){
        //     $course = Course::findOrFail($id);
        //     $course->status = 1;
        //     $course->save();
        //     $msg = "Course Published successfully";
        // }

        // save chapter Data
        if ($save == 1) {
            $notification = [];
            $tags = null;
            if (request()->has('tags')) {
                $tags
                = implode(',', $request['tags']);
            }

            // update info
            $hour = isset($request->hours) ? $request->hours : 0;
            $minutis = isset($request->minutes) ? $request->minutes : 0;
            $whole_video_coin = isset($request->whole_video_coin) ? $request->whole_video_coin : 0;

            $duration = $hour . ':' . $minutis;
            $chapter = ChapterInfo::findOrFail($chapterId);
            $chapter->title = request()->has('title') ? $request['title'] : null;
            $chapter->whole_video_coin = $whole_video_coin;
            $chapter->allow_download = request()->has('allow_download') ? 1 : 0;
            $chapter->allow_sleep = $request->allow_sleep;
            $chapter->plan_id = request()->has('plan_id') ? $request['plan_id'] : null;
            $chapter->tags = $tags;
            $chapter->enable_sharing = request()->has('enable_sharing') ? $request['enable_sharing'] : 0;
            $chapter->availability_setting = request()->has('availability_setting') ? $request['availability_setting'] : null;
            $chapter->description = request()->has('description') ? $request['description'] : null;
            $chapter->available_till = (request()->has('availability_setting') && $request['availability_setting'] == 1 && request()->has('available_till')) ? $request['available_till'] : "0000-00-00 00:00:00";
            $chapter->available_from = (request()->has('availability_setting') && $request['availability_setting'] == 1 && request()->has('available_from')) ? $request['available_from'] : "0000-00-00 00:00:00";
            $chapter->enable_watermark = request()->has('enable_watermark') ? $request['enable_watermark'] : 0;
            $chapter->timer = request()->has('timer') ? $request['timer'] : 0;
            // $chapter->duration = request()->has('duration') ? $request['duration'] : NULL;
            // $chapter->duration = request()->has('duration_in_seconds') ? $request['duration_in_seconds'] : null;
            $chapter->duration = $duration;
            $chapter->autoplay = request()->has('autoplay') && $request->autoplay == 1 ? 1 : 0;
            $chapter->updated_by = auth()->id();
            $chapter->save();

            if (request()->has('file_url') && (isset($request['file_url']))) {
                if ((isset($request['file_url']))) {
                    $chapterdata = Chapter::findOrFail($chapter->chapter_id);
                    $chapterdata->file_url = request()->has('file_url') ? $request['file_url'] : null;
                    $chapterdata->save();
                }
            }

            $msg = "Chapter updated successfully";
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = $msg;
            return redirect()->back()->with('notification', $notification);
        }

    }

    public function image(Request $request)
    {

        // dd($request->all());
        $notification = [];
        //dd($request->all());
        $request->validate([
            'courseid' => 'required',
            'image' => 'required|image',
        ]);

        //dd('test');
        $path = $request->file('image')->store('courses');
        if (!empty($path)) {
            Course::where('id', $request->courseid)->update(['image' => $path]);
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Course image updated successfully";
        return redirect()->back()->with('notification', $notification);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function thumbnailImage(Request $request)
    {
        // dd($request->all());
        $data = array();
        $id = $request->chapterId;
        $chapter = ChapterInfo::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:jpeg,png', //,csv,txt,pdf
            //  'file' => 'required|mimes:jpeg,png|max:2048|dimensions:min_width=1366,max_width=1920,min_height=300,max_height=350'//,csv,txt,pdf
        ]);

        if ($validator->fails()) {

            $data['success'] = 0;
            $data['error'] = $validator->errors()->first('file'); // Error response

        } else {
            if ($request->file('file')) {
                //remove Old file
                if (!empty($chapter->thumbnail)) {
                    Storage::delete($chapter->thumbnail);
                }
                $file = $request->file('file');

                $filename = time() . '_' . $file->getClientOriginalName();

                // File extension
                $extension = $file->getClientOriginalExtension();

                // File upload location
                $location = $file->storeAs('admin/thumbnail', $filename);

                // File path
                $path = Storage::url($location);

                // Update Image
                $chapter->thumbnail = $location;

                $chapter->save();
                $chapterData = Chapter::with('media')->findOrFail($chapter->chapter_id);
                if (isset($chapterData->media) && $chapterData->media->videoId) {
                    $isUpload = getVideoPoster($chapterData->media->videoId, $file);
                }
// dd("")
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

    /***
     * Import from asset code
     */
    public function importFromAsset(Request $request)
    {
        $query = Media::with('createdBy:id,name')
            ->select('id', 'title', 'updated_at', 'path', 'created_at', 'created_by')
            ->whereNull('deleted_at')
            ->latest()
            ->get();

        return DataTables::of($query)
            ->editColumn('path', function ($row) {
                if (Storage::exists($row->path) && !empty($row->path)) {
                    $extension = pathinfo(Storage::url($row->path), PATHINFO_EXTENSION);
                    if (strtolower($extension) == 'jpg' || strtolower($extension) == 'jpeg' || strtolower($extension) == 'png' || strtolower($extension) == 'gif') {
                        return '<a href="' . Storage::url($row->path) . '" target="_blank"><img src="' . Storage::url($row->path) . '" style="height: 60px;width: 60px;"></a>';
                    } else {
                        return '<a target="_blank" href="' . Storage::url($row->path) . '"><i class="fa fa-download" aria-hidden="true"></i></a>';
                    }
                } else {
                    return 'No Image';
                }
            })
            ->editColumn('updated_at', function ($row) {
                return !empty($row->updated_at) ? date('d M, Y', strtotime($row->updated_at)) : '';
            })
            ->editColumn('created_at', function ($row) {
                return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
            })
            ->editColumn('created_by', function ($row) {
                return !empty($row->createdBy) ? $row->createdBy->name : '';
            })
            ->editColumn('action', function ($row) {
                return '';
            })->rawColumns(['path', 'created_at'])
            ->toJson();
    }

    public function delete($id)
    {

        if (!$this->user->can('delete_courses')) {
            abort(403);
        }

        $notification = [];
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // Remove chapter info and chapters
        ChapterInfo::whereIn('chapter_id', function ($query) use ($id) {
            $query->select('id')
                ->from('chapters')
                ->where('course_id', $id);
        })->forceDelete();
        $chp = Chapter::where("course_id", $id)->get();
        foreach ($chp as $val) {
            UserCourseProgress::where('chapter_id', $val->id)->forceDelete();
            Chapter::where('course_id', $val->id)->forceDelete();
        }

        // Remove course plans, course packages, wishlist, notifications, user courses, rating reviews, and course categories
        CoursePlan::where('course_id', $id)->forceDelete();
        CoursePackage::where('course_id', $id)->forceDelete();
        Wishlist::where('course_id', $id)->forceDelete();
        Notification::where('courseId', $id)->forceDelete();
        UserCourse::where('course_id', $id)->forceDelete();
        RatingReview::where('course_id', $id)->forceDelete();
        CourseCategory::where('course_id', $id)->forceDelete();

        // Set slider course to null
        Slide::where('action_url_mobile', $id)->update(['action_url_mobile' => null]);

        // Finally, delete the course itself
        Course::where('id', $id)->forceDelete();

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Course deleted successfully";

        return redirect()->back()->with('notification', $notification);
    }

    /**
     * Permanant Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function bulk_Hard_Delete(Request $request)
    {

        if (!$this->user->can('delete_courses')) {
            abort(403);
        }

        $notification = [];
        if (!empty($request->bd) > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($request->bd as $id => $value) {
                //course chapter and chapter info removed
                // $chapterIds = Chapter::where('course_id', $id)->select('id')->get()->toArray();
                // if (isset($chapterIds) && count($chapterIds) > 0) {
                //     $chapterInfo = ChapterInfo::whereIn('chapter_id', $chapterIds)->delete();
                //     $chapter = Chapter::whereIn('id', $chapterIds)->delete();
                // }

                // // course_plans removed
                // $coursePlan = CoursePlan::where('course_id', $id)->delete();

                // //course packages removed
                // $coursePackage = coursePackage::where('course_id', $id)->delete();

                // //Wishlist removed
                // $wishList = Wishlist::where('course_id', $id)->delete();

                // // slider course null
                // Slide::where('action_url_mobile', $id)->update(['action_url_mobile' => null]);

                // $course = Course::withTrashed()->where('id', $id)->firstOrFail();
                // $course->forceDelete();

                ChapterInfo::whereIn('chapter_id', function ($query) use ($id) {
                    $query->select('id')
                        ->from('chapters')
                        ->where('course_id', $id);
                })->forceDelete();

                $chp = Chapter::where("course_id", $id)->get();
                foreach ($chp as $val) {
                    UserCourseProgress::where('chapter_id', $val->id)->forceDelete();
                    Chapter::where('course_id', $val->id)->forceDelete();
                }

                // Chapter::where('course_id', $id)->forceDelete();

                CoursePlan::where('course_id', $id)->forceDelete();
                CoursePackage::where('course_id', $id)->forceDelete();
                Wishlist::where('course_id', $id)->forceDelete();
                Notification::where('courseId', $id)->forceDelete();
                UserCourse::where('course_id', $id)->forceDelete();
                RatingReview::where('course_id', $id)->forceDelete();
                CourseCategory::where('course_id', $id)->forceDelete();

                Slide::where('action_url_mobile', $id)->update(['action_url_mobile' => null]);
                Course::where('id', $id)->forceDelete();
            }

            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Courses deleted successfully";
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No Course selected";
        }
        return redirect()->back()->with('notification', $notification);
    }

    public function addable_learners(Request $request, $courseId, $learnerId, $planId = null)
    {

        $notification = [];
        $mobile = [];
        $email = [];
        $ids = [];
        $learner_count = 0;
        if (!empty($courseId) && !empty($learnerId)) {
            $learners = explode(',', $learnerId);
            $learner_count = count($learners);
            if ($learner_count > 0) {
                foreach ($learners as $key => $value) {
                    DB::statement('SET FOREIGN_KEY_CHECKS = 0');
                    $expiredAt = get_plan_expire($planId);
                    $learnerData = Learner::with('country:id,phonecode')->where("id", $value)->get()->first();
                    $ids[] = $learnerData->id;
                    $coursePlanData = CoursePlan::where("id", $planId)->get()->first();
                    $course_title = Course::where('id', $courseId)->first()->title;
                    $userCourse = UserCourse::create([
                        'course_id' => $courseId,
                        'learner_id' => $value,
                        'plan_id' => $planId,
                        'order_status' => 4,
                        'expire_at' => $expiredAt,
                        'price' => (!empty($coursePlanData)) ? $coursePlanData->final_payable_price : 0.00,
                        'full_name' => (!empty($learnerData)) ? $learnerData->name : null,
                        'email' => (!empty($learnerData)) ? $learnerData->email : null,
                        'mobile' => (!empty($learnerData)) ? $learnerData->mobile : null,
                        'country_id' => (!empty($learnerData)) ? $learnerData->country_id : null,
                        'state_id' => (!empty($learnerData)) ? $learnerData->state_id : null,
                        'city_id' => (!empty($learnerData)) ? $learnerData->city_id : null,
                    ]);
                    $user_course_id = $userCourse->id;
                    DB::statement('SET FOREIGN_KEY_CHECKS = 1');
                    $email[] = $learnerData->email;
                    $mobile[] = $learnerData->mobile;

                    $content = "You are enrolled in course " . $course_title;
                    $subject = "Enrolled in Course";
                    $params['common'] = [
                        'course_id' => $courseId,
                        'learner_id' => $learnerData->id,
                        'type' => 3,
                    ];

                    $params['email']['user_course_id'] = $user_course_id;
                    $params['email']['to'] = $email;

                    $params['push']['to'] = implode(",", $ids);

                    $params['web']['to'] = $ids;

                    // need to append country code with phone number dynamically
                    $params['whatsapp']['to'] = $learnerData->country->phonecode . "" . $learnerData->mobile;
                    $params['whatsapp']['message'] = $course_title; //"You are enrolled in course ".
                    $params['whatsapp']['template'] = 'course_enrolled'; //"You are enrolled in course ".
                    send_notification($params, 'enroll', ['whatsapp', 'email', 'push', 'web'], $subject, $content);
                }

            }
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Learner added to the course successfully";
        if ($learner_count > 1) {
            $notification['msg'] = "Learners added to the course successfully";
        }
        return redirect()->route("courses.learners", ['id' => $courseId])->with('notification', $notification);
    }

    public function video_title(Request $request)
    {

        // dd(config('settings.youtube_api_key'));
        ini_set('max_execution_time', 300);
        $request->validate([
            'url' => 'required|filled|url',
        ]);
        $title = "";
        $yt = str_contains($request->url, "youtube.com");
        $vim = str_contains($request->url, "vimeo.com");

        if ($yt) {
            $key = config()->has('settings.youtube_api_key') ? config('settings.youtube_api_key') : null;

            $video = Http::withHeaders([
                'Accept' => 'application/json',
            ])->get('https://www.googleapis.com/youtube/v3/search', [
                'part' => 'snippet',
                'maxResults' => 1,
                'key' => $key,
                'q' => $request->url,
            ]);

            $x = $video->json();

            if (isset($x['items'][0]['snippet']['title'])) {
                $title = htmlspecialchars_decode($x['items'][0]['snippet']['title'], ENT_QUOTES);
            } else {

                $errorMessage = 'An error occurred while fetching the video title.';
            }

        } elseif ($vim) {

            $VIMEO_VERSION_KEY = config()->has('settings.vimeo_version') ? config('settings.vimeo_version') : null;
            $VIMEO_ACCESS_KEY = config()->has('settings.vimeo_access') ? config('settings.vimeo_access') : null;
            $url = $request->url;
            $arr = explode("/", $url);
            $vid = end($arr);
            $vim_api = "https://api.vimeo.com/videos/" . $vid;
            $response = Http::withHeaders([
                'Authorization' => 'bearer ' . $VIMEO_ACCESS_KEY,
                'Accept' => 'application/vnd.vimeo.*+json;version=3.4',
            ])->get($vim_api, [
                'fields' => 'name',
            ]);
            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['name'])) {
                    $title = htmlspecialchars_decode($responseData['name'], ENT_QUOTES);
                }
            } else {

                $statusCode = $response->status();
                // $errorMessage = $response->json('error') ?? 'An error occurred while fetching the video title.';
                $errorMessage = 'An error occurred while fetching the video title.';
            }
        } else {
            $title = "";
        }

        if (!empty($title)) {
            return response()->json(["msg" => $title, "status" => 1]);
        } else {
            return response()->json(["msg" => $errorMessage, "status" => 0]);
        }
    }

    public function builderOrdering(Request $request)
    {

        // sectionId = Parent ID
        // questionId = Child draggable ID
        // questionList = Child new ordering array
        // sectionList = Parent new ordering array

        if (request()->has('sectionId') && request()->filled('sectionId')) {
            /**
             * For Parent
             */
            if (request()->has('sectionList')) {
                if (isset($request->sectionList) && !empty($request->sectionList) && count($request->sectionList) > 0) {
                    foreach ($request->sectionList as $key => $value) {
                        Chapter::where('id', $value)->update(['order' => $key]);
                    }
                }
            }
            /**
             * For Child
             */
            if (request()->has('questionList')) {
                $questionList = request()->input('questionList');

                if (!empty($questionList)) {

                    foreach ($questionList as $key => $chapterId) {

                        Chapter::where('id', $chapterId)->update(['order' => $key]);
                    }
                }
            }

        }
        return response()->json([
            'success' => true,
            'message' => 'Your order has been saved successfully!.',
        ], 200);

    }

    public function statuscheck($videoid)
    {
        $curl = curl_init();
        $api_key = config()->has('settings.vdocipher_key') ? config('settings.vdocipher_key') : null;
        if (isset($api_key) && $api_key != null) {
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://dev.vdocipher.com/api/videos/" . $videoid,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Accept: application/json",
                    "Authorization: Apisecret $api_key",
                ),
            ));

            $response = curl_exec($curl);
            // dd()
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                return response()->json(false, 200);
            } else {
                $res = json_decode($response);
                if ($res->status === "ready") {
                    return response()->json(true, 200);
                }
                return response()->json(false, 200);
            }
        }
    }

    public function introVideo(Request $request)
    {

        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

        if (!$receiver->isUploaded()) {
            return "error";
        }
        $fileReceived = $receiver->receive();

        if ($fileReceived->isFinished()) {
            // dd("oiuo");
            $file = $fileReceived->getFile();
            $isUpload = intro_upload_vdo_cipher($request->course_id, $file);
            unlink($file->getPathname());
            $response = [
                'icon' => 'success',
                'title' => 'success',
                'message' => 'File uploaded successfully!',
                'status' => 1,
            ];
            return response()->json($response);
        }
        return [
            'status' => false,
            'message' => 'Error',
        ];
    }

}
