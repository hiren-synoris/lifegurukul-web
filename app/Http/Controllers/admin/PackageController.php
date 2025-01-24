<?php

namespace App\Http\Controllers\admin;

use App\Models\User;
use App\Models\Slide;
use App\Helper\Helper;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\Learner;
use App\Models\Support;
use App\Models\Wishlist;
use App\Models\CoursePlan;
use App\Models\UserCourse;
use App\Models\ChapterInfo;
use App\Models\Instructors;
use Illuminate\Http\Request;
use App\Models\CoursePackage;
use App\Models\CourseCategory;
use App\Models\DropdownOption;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\RenewingSubscriptions;
use App\Mail\SupportTicketCreatedMail;
use Illuminate\Support\Facades\Storage;

class PackageController extends Controller
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
        if (!$this->user->can('browse_packages')) abort(403);
        $pg_header = "Packages";
        if (view()->exists('admin.packages.list')) {
            return view('admin.packages.list', compact('pg_header'));
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
        if (!$this->user->can('add_packages')) abort(403);
        $instructors = Helper::getInstructure();

        $courseCategories = DropdownOption::getDropdownCategories('course_category')
        ->select('id','dropdown_id','name')
        ->where('status','1')
        ->get();

        $pg_header = "Add Package";

        if (view()->exists('admin.packages.add')) {
            return view('admin.packages.add', compact('instructors','courseCategories','pg_header'));
        }
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$this->user->can('add_packages')) abort(403);
        $validated = $request->validate([
            'title' => 'required',
            'slug' =>'required|filled|max:255|unique:courses,slug|alpha_dash',
            'type' => 'required',
            'category_id' => 'required|array',
            'instructor_id' => 'required',
            // 'course_coin' =>'required',
        ],[
            "category_id.required"=>"The category field is required.",
            "instructor_id.required"=>"The instructor field is required.",
        ]);

        try {
            $notification = [];
            DB::beginTransaction();
            $package = Course::create([
                'title' => allowWhiteSpace($request['title']),
                'instructor_id' => $request['instructor_id'],
                'slug' => $request['slug'],
                'type' => $request['type'],
                'course_coin' => $request['course_coin'],
                'course_platform' => '1,2,3',
                'order' => request()->has('order') ? $request->order : NULL,
                'status' => request()->has('status') ? 1 : 0,
                'created_by' => auth()->id(),
                'is_featured' => request()->has('featured') ? 1 : 0,
                'is_free' => request()->has('is_free') ? 1 : 0,
                "course_finished"=>$request->course_finished,
                "course_finished_day"=>$request->course_finished_day,
                "days"=>$request->days
            ]);
            // dd($package);

            $data['lng'] = ($request['lng'])? $request['lng'] : 1;

            if(request()->has('category_id') && !empty($request->category_id)){
                //$data['course_category']=$request['category_id']??NULL;
                Course::findOrFail($package->id)->update($data);
                foreach($request->category_id as $key => $value){
                    CourseCategory::updateOrCreate([
                        'course_id' => $package->id,
                        'category_id' => $value
                    ],[
                        'course_id' => $package->id,
                        'category_id' => $value,
                        'created_by' => auth()->id()
                    ]);
                }
            }

            DB::commit();

            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Package created successfully";
            return redirect()->route('packages.edit', ['package' => $package->id])->with('notification', $notification);

        } catch (\Exception $e) {
            DB::rollback();
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "Something went wrong.";
        }
        return redirect('backoffice/packages')->with('notification', $notification);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        if (!$this->user->can('edit_packages')) abort(403);
        $instructors = Helper::getInstructure();

        $course = Course::with(['categories' => function($query){
            $query->whereNull('course_categories.deleted_at');
        }])->where('id', $id)
        ->firstOrFail();



        $courseCategories = DropdownOption::getDropdownCategories('course_category')
        ->select('id','dropdown_id','name')
        ->where('status','1')
        ->get();
        $renewingSubscriptions = RenewingSubscriptions::get();
        if(view()->exists('admin.packages.edit')){
            return view('admin.packages.edit', compact('course', 'instructors', 'courseCategories',"renewingSubscriptions"));
        } abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        // dd($request->all());
        // video/mp4,video/x-m4v,video/*

        if (!$this->user->can('edit_packages')) abort(403);
        $validated = $request->validate([
            'title' => 'required',
            'slug' =>'required|filled|max:255|unique:courses,slug,'.$id,
            'type' => 'required',
            'category_id' => 'required|array',
            'instructor_id' => 'required',
            'intro_video' => 'mimetypes:video/*',
            'banner_image' => 'image|mimes:jpg,png',
            // 'course_coin' =>'required',
        ],[
            "category_id.required"=>"The category field is required.",
            "instructor_id.required"=>"The instructor field is required.",
            "banner_image."=>"The banner image must be less than 500 kb."
        ]);

        $notification = [];
        $coursePlatform = NULL;

        if(request()->has('course_platform')){
            $coursePlatform = implode(',',$request['course_platform']);
        }
        $course = Course::findOrFail($id);
        $introVideo = $course->intro_video;
        if(request()->hasFile('intro_video')){
            if(!empty($course->intro_video)){
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
        if(request()->hasFile('banner_image')){
            if(!empty($course->banner_image)){
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
        $tags = NULL;
        $keywords=NULL;
        if (request()->has('tags')) {

            $tags = implode(',', $request['tags']);
        }
        if(request()->has('meta_keywords')){
            $keywords=implode(',',$request['meta_keywords']);
        }
        $data=[];
        if($request['title']){
            $data['title']=$request['title'];
        }
        if($request['instructor_id']){
            $data['instructor_id'] = $request['instructor_id'] ? $request['instructor_id'] : NULL;
        }
        if($request['instructor_name']){
            $data['instructor_name'] = $request['instructor_name'] ? $request['instructor_name'] : NULL;
        }
        //if(request()->has('course_platform')){
            $data['course_platform'] = $coursePlatform;
      //  }
        if(request()->has('order')){
            $data['order'] = $request['order'];
        }

        $data['status'] = request()->has('status') ? 1 : 0;
        $data['updated_by'] = auth()->id();
        $data['is_featured'] = request()->has('featured') ? 1 : 0;
        $data['is_free'] = request()->has('is_free') ? 1 : 0;
        $data['public_forum_status'] = $request->public_forum_status=="on" ? 1 : 0;
        $data['show_learner_cnt'] = ($request['show_learner_cnt'] != null && $request['show_learner_cnt'] == 'on')? 1 : 0;
        // $data['show_validity_learner'] = ($request['show_validity_learner'] != null && $request['show_validity_learner'] == 'on')? 1 : 0;
        // $data['allow_offline_data'] = ($request['allow_offline_data'] != null && $request['allow_offline_data'] == 'on')? 1 : 0;
        $data['allow_bookmark'] = ($request['allow_bookmark'] != null && $request['allow_bookmark'] == 'on')? 1 : 0;

        if(request()->has('banner_image')){
            $data['banner_image'] = $bannerImage;
        }
        if(request()->has('intro_video')){
            $data['intro_video'] = $introVideo;
        }

        $data['description'] = $request['description'] ? $request['description'] : NULL;
        $data['course_coin'] = $request['course_coin'] ? $request['course_coin'] : NULL;
        $data['tagline'] = $request['tagline'] ? $request['tagline'] : NULL;
        $data['how_to_use'] = $request['how_to_use'] ? $request['how_to_use'] : NULL;
        $data['lng'] = ($request['lng'])? $request['lng'] : 1;
        $data['tags'] = $tags;
        $data['hours'] = request()->has('hours') ? $request['hours'] : 0;
        $data['minutes'] = request()->has('minutes') ? $request['minutes'] : 0;
        $data["completely_watch"]=$request->completely_watch;
        $data["course_finished"]=$request->course_finished;
        $data["course_finished_day"]=$request->course_finished_day;
        $data["days"]=$request->days;


        //$data['course_category']=$request['category_id']??NULL;
        Course::findOrFail($id)->update($data);

        if(request()->has('category_id') && !empty($request->category_id)){
            CourseCategory::where('course_id', $id)->whereNotIn('category_id',$request->category_id)->delete();
            foreach($request->category_id as $key => $value){
                CourseCategory::updateOrCreate([
                    'course_id' => $id,
                    'category_id' => $value
                ],[
                    'course_id' => $id,
                    'category_id' => $value,
                    'created_by' => auth()->id()
                ]);
            }
        }

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Package updated successfully";
        return redirect()->back()->with('notification', $notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        if (!$this->user->can('delete_packages')) abort(403);
        $notification = [];
        $course = Course::where('id', $id)->firstOrFail();
        $course->delete();
        coursePackage::where('id', $id)->delete();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Package deleted successfully";
        return redirect()->back()->with('notification', $notification);
    }

    /**
     * Restore specified record .
     */
    public function restore($id)
    {
        if (!$this->user->can('restore_packages')) abort(403);
        $notification = [];
        $course = Course::withTrashed()->where('id', $id)->firstOrFail();
        $course->restore();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Package restored successfully";

        return redirect()->back()->with('notification', $notification);
    }

    /**
     * Restore all Packages records.
     */
    public function restore_all(Request $request)
    {
        if (!$this->user->can('restore_packages')) abort(403);
        foreach (array_keys($request->selected_checkbox) as $key => $value) {
            $course = Course::withTrashed()->where('id', $value)->firstOrFail();
            $course->restore();
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Packages restored successfully";
        return redirect()->back()->with('notification', $notification);
    }

    /**
     * Package SEO update
     */
    public function seoUpdate(Request $request, $id)
    {

        // $request->validate([
        //     'meta_title' => 'required',
        //     'meta_keywords' => 'required|array',
        //     'meta_description' => 'required',
        // ]);

        if (!$this->user->can('edit_packages')) abort(403);
        $notification = [];
        if(request()->has('meta_keywords')){
            $meta_keywords = implode(',',$request['meta_keywords']);
        }

        Course::findOrFail($id)->update([
            'meta_title' => request()->has('meta_title') ? $request['meta_title'] : NULL,
            'meta_keywords' => $meta_keywords ?? NULL,
            'meta_description' => request()->has('meta_description') ? $request['meta_description'] : NULL,
            'updated_by' => auth()->id()
        ]);

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Package updated successfully";
        return redirect()->back()->with('notification', $notification);
    }

    /**
     * Package builder code
     *
     */
    public function coursePackage(Request $request, $id)
    {
        $course = [];
        if (!$this->user->can('edit_packages')) abort(403);
        $user = auth()->user();
        $courseQuery = Course::where('id', $id);

        if ($user->hasRole(User::INSTRUCTOR)) {
            $courseQuery->where('instructor_id', $user->id);
        }

        $courseData = $courseQuery->firstOrFail();

        $courseQuery = Course::where('type', Course::COURSE);

        if ($user->hasRole(User::INSTRUCTOR)) {
            $courseQuery->where('instructor_id', $user->id);
        }

        $course = $courseQuery->first();

        // dd($course);

        try {
            if (view()->exists('admin.packages.listing.index')) {
                return view('admin.packages.listing.index',[
                    'course' => $course,
                    'courseData'=> $courseData,
                ]);
            } abort(404);
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
        if (!$this->user->can('edit_packages')) abort(403);
        $user = auth()->user();

        // $baseQuery = Course::with(['instructor:id,name', 'categories:id,name'])
        //     ->where('type', Course::COURSE)
        //     // ->whereHas("coursePlan",function($baseQuery){
        //     //     $baseQuery->where("status","1");
        //     // })
        //     // ->where('status', 1)
        //     ->whereNull('deleted_at')
        //     ->select('id', 'title', 'image', 'instructor_id', 'status', 'created_at', 'deleted_at')
        //     ->latest();

        // if ($user->hasRole(User::INSTRUCTOR)) {
        //     $course = Course::where('instructor_id', $user->id)->findOrFail($id);
        //     $query = $baseQuery->where('instructor_id', $user->id)->get();
        // } else {
        //     $course = Course::findOrFail($id);
        //     $query = $baseQuery->get();
        // }

        // $baseQuery->filter(function($val){
        //     $res = CoursePackage::where("package_id", $course->id)
        //     ->where('course_id', $val->id)->first();
        // });

        $course = Course::findOrFail($id);

        $baseQuery = Course::with(['instructor:id,name', 'categories:id,name'])
            ->leftJoin('course_packages', function ($join) use ($course) {
                $join->on('courses.id', '=', 'course_packages.course_id')
                     ->where('course_packages.package_id', $course->id);
            })
            ->where('courses.type', Course::COURSE)
            ->whereNull('courses.deleted_at')
            ->select(
                'courses.id',
                'courses.title',
                'courses.image',
                'courses.instructor_id',
                'courses.status',
                'courses.created_at',
                'courses.deleted_at',
                'course_packages.id as course_package_id'
            )
            ->orderByDesc('course_package_id');

            if ($user->hasRole(User::INSTRUCTOR)) {
                $baseQuery->where('instructor_id', $user->id);
            }

            $query = $baseQuery->latest()->get();

        return DataTables::of($query)
            ->addColumn('action', function ($row) use ($course) {
                $button = "";
                // $res = CoursePackage::where("package_id", $course->id)
                // ->where('course_id', $row->id)->first();
                // delete icon
                if($row->course_package_id){
                    if ($this->user->can('delete_packages')) {
                        $addurl = route('delete.packages.package', ['course_id' => $course->id,'package_id' => $row->id]);
                        $button .= '<a class="mx-1 text-danger " title="Add" href="' . $addurl. '"><i class="fas fa-trash-alt"></i></a>';
                    }
                    return "<div class='d-flex justify-content-center'>$button</div>";
                }

                else{
                    if ($this->user->can('edit_packages')) {
                        $addurl = route('add.packages.package', ['course_id' => $course->id,'package_id' => $row->id]);
                        $button .= '<a class="mx-1 text-success " title="Add" href="' . $addurl. '"><i class="fas fa-plus-square"></i></a>';
                    }
                    return "<div class='d-flex justify-content-center'>$button</div>";
                }

            })
            ->editColumn('image', function($row){
                $pictureURL = getImageIfExists($row->image);
                return '<a href="'.$pictureURL.'" target="_blank"><img src="'.$pictureURL.'" style="height: 60px;width: 60px;"></a>';
            })
            ->editColumn('instructor_name', function($row){
                return isset($row->instructor) && !empty($row->instructor) ? $row->instructor->name : '';
            })
            // ->editColumn('course_category', function($row){
            //     return Helper::getCategoryName($row->course_category);
            // })
            ->editColumn('status', function($row){
                return Helper::checkPublish($row->status);
            })
            ->editColumn('date', function($row){
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action', 'image','status','date'])
            ->addIndexColumn()
            ->toJson();
    }

    /**
     * Add course to package
     */
    public function addCourseToPackage(Request $request, $course_id, $package_id)
    {
        if(!empty($package_id) && !empty($course_id)){
                CoursePackage::updateOrCreate([
                    'package_id' => $course_id,
                    'course_id' => $package_id
                ],[
                    'package_id' => $course_id,
                    'course_id' => $package_id,
                    'created_by' => auth()->id()
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
        if(request()->has('bd') && !empty($course_id)){
            foreach($request->bd as $key => $value){
                CoursePackage::updateOrCreate([
                    'package_id' => $course_id,
                    'course_id' => $key
                ],[
                    'package_id' => $course_id,
                    'course_id' => $key,
                    'created_by' => auth()->id()
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
           foreach($request->bd as $course_id => $value){
                $res = CoursePackage::where("package_id",$package_id)
                            ->where('course_id',$course_id)->first();
                if($res){
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

    public function courseLearners(Request $request, $id)
    {
        if (!$this->user->can('edit_packages')) abort(403);
        try {
            if (view()->exists('admin.packages.learners.index')) {

                $course = Course::where('id',$id)->with(['packages','plans'=>function($query){
                    return $query->where('status',1);
                }])->first();
                // dd($course);
                return view('admin.packages.learners.index', [
                    'courseId' => $id,
                    'course' => $course,
                    'plans' => $course->plans->flatten()
                ]);
            }
            abort(404);
        } catch (\Exception $e) {
            return abort(404);
        }
    }


    public function getPackages()
    {

        if (!$this->user->can('browse_packages')) abort(403);
        if($this->user->hasRole(User::INSTRUCTOR)){
            $query = Course::listing()->isPackage()->notDeleted()->where('instructor_id',$this->user->id)
            ->withCount(['coursePlan' => function ($query) {
                $query->where('status', 1);
                $query->where('created_at', '<', Carbon::now());

            }])
            ->get();
        }
        else{

            $query = Course::listing()
            ->withCount(['coursePlan' => function ($query) {
                $query->where('status', 1);
                $query->where('created_at', '<', Carbon::now());

            }])
            // ->withCount(['userCourseCount' => function ($query) {
            //     $query->where('created_at', '<', Carbon::now());
            // }])
            ->with(['userCourseCount' => function ($query) {
                $query->selectRaw('course_id, COUNT(DISTINCT learner_id) as user_course_count')
                    ->groupBy('course_id');
            }])
            ->isPackage()->notDeleted()->get();
        }
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("packages.destroy", ["package" => $row->id]);
                $button = "";

                $disabled="";
                if($row->approval_status == 1){
                    $disabled=" disabled_cls";
                }

                if (auth()->user()->hasRole(User::INSTRUCTOR) && $row->status !== 1) {
                    $button .= '<a class="mx-1 '.$disabled.' " title="Ask for Approval" href="' . route('packages.ask.approval', ['course_id' => $row->id]) . '"><i class="fas fa-check-circle text-green"></i></a>';
                   }

                if ($this->user->can('edit_packages')) {
                    $button .= '<a class="mx-1" title="Edit" href="' . url('backoffice/packages/' . $row->id . '/edit') . '"><i class="fas fa-edit text-orange"></i></a>';

                    $button .= '<a class="mx-1" title="Package Builder" href="' . url('backoffice/packages/' . $row->id . '/package') . '"><i class="fas fa-tools text-red"></i></a>';

                    $button .= '<a class="mx-1" target="_blank" title="Landing Page" href="' . url('course-package/' . $row->slug) . '"><i class="fas fa-eye  text-orange"></i></a>';

                    $button .= '<a class="mx-1" title="Learners" href="' . url('backoffice/packages/' . $row->id . '/learners') . '"><i class="fas fa-user-friends text-purple"></i></a>';
                }

                if (is_null($row->deleted_at)) {
                    if ($this->user->can('delete_packages')) {
                        $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                    }
                } else {
                    if ($this->user->can('restore_packages')) {
                        $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/packages/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                }

                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('image', function($row){
                $pictureURL = getImageIfExists($row->image,course_img_default());
                return '<a href="'.$pictureURL.'" target="_blank"><img src="'.$pictureURL.'" style="height: 60px;width: 60px;"></a>';
            })
            ->editColumn('title', function ($row) {
                return isset($row->title) && !empty($row->title) ? '<a href="' . route('packages.edit', ['package' => $row->id]) . '">' . $row->title . '</a>' : '';
            })
            ->editColumn('status', function ($row) {
                return Helper::checkPublish($row->status);
            })
            ->editColumn('type', function ($row) {
                return Helper::checkType($row->type);
            })
            ->editColumn('course_plan_count', function ($row) {

                $url = url("backoffice/packages/$row->id/edit");

                return '<a href="' . $url . '">' . $row->course_plan_count . '</a>';
            })
            ->editColumn('user_course_count', function ($row) {

                $url = url("backoffice/packages/$row->id/learners");

                $collection = collect($row->userCourseCount);
                return $collection->sum('user_course_count');
            })

            ->editColumn('created_at', function($row){
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['type','image','action', 'status', 'title', 'created_at','course_plan_count','user_course_count'])
            ->addIndexColumn()
            ->toJson();
    }

    /**
     * Get deleted Packages records.
     */
    public function getPackagesDeleted()
    {
        if (!$this->user->can('browse_packages')) abort(403);

        $query = Course::listing()->isPackage()
        // ->withCount(['coursePlan' => function ($query) {
        //     $query->where('status', 1);
        //     $query->where('created_at', '<', Carbon::now());

        // }])
        ->getDeleted()->get();

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("packages.destroy", ["package" => $row->id]);
                $button = "";
                $deleteurl = route("packages.delete", ["id" => $row->id]);

                if ($this->user->can('delete_packages')) {
                    $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=permanent_delete_confirmation("' . $deleteurl . '")><i class="fas fa-trash-alt"></i></a>';
                }
                if ($this->user->can('restore_packages')) {
                    $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/packages/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('image', function($row){
                $pictureURL = getImageIfExists($row->image);
                return '<a href="'.$pictureURL.'" target="_blank"><img src="'.$pictureURL.'" style="height: 60px;width: 60px;"></a>';
            })
            ->editColumn('title', function ($row) {
                return isset($row->title) && !empty($row->title) ? '<a href="' . route('packages.edit', ['package' => $row->id]) . '">' . $row->title . '</a>' : '';
            })
            ->editColumn('status', function ($row) {
                return Helper::checkPublish($row->status);
            })
            ->editColumn('type', function ($row) {
                return Helper::checkType($row->type);
            })
            ->editColumn('created_at', function($row){
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->editColumn('user_course_count' ,function ($raw) {
                return 0 ;
            })
            ->editColumn('course_plan_count', function ($row) {

               return 0 ;
            })
            ->rawColumns(['type','image','action', 'status', 'title', 'created_at',"course_plan_count","user_course_count"])
            ->addIndexColumn()
            ->toJson();
    }

    public function delete($id)
    {

        if (!$this->user->can('delete_packages')) abort(403);
        $notification = [];
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

            //course chapter and chapter info removed
            $chapterIds = Chapter::where('course_id', $id)->select('id')->get()->toArray();
            if(isset($chapterIds) && count($chapterIds) > 0){
                $chapterInfo = ChapterInfo::whereIn('chapter_id', $chapterIds)->delete();
                $chapter = Chapter::whereIn('id', $chapterIds)->delete();

            }

            // course_plans removed
            CoursePlan::where('course_id', $id)->delete();

            //course packages removed
            coursePackage::where('course_id', $id)->delete();

            //Wishlist removed
            Wishlist::where('course_id', $id)->delete();

            // slider course null
            Slide::where('action_url_mobile', $id)->update(['action_url_mobile' => null]);

            $course= Course::withTrashed()->where('id', $id)->firstOrFail();
            $course->forceDelete();

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Package deleted successfully";

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
        // dd("");
        if (!$this->user->can('delete_packages')) abort(403);
        $notification = [];
        if (!empty($request->bd) > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($request->bd as $id => $value) {
                //course chapter and chapter info removed
                $chapterIds = Chapter::where('course_id', $id)->select('id')->get()->toArray();
                if(isset($chapterIds) && count($chapterIds) > 0){
                    $chapterInfo = ChapterInfo::whereIn('chapter_id', $chapterIds)->delete();
                    $chapter = Chapter::whereIn('id', $chapterIds)->delete();
                }

                // course_plans removed
                CoursePlan::where('course_id', $id)->delete();

                //course packages removed
                coursePackage::where('course_id', $id)->delete();

                //Wishlist removed
                Wishlist::where('course_id', $id)->delete();

                // slider course null
                Slide::where('action_url_mobile', $id)->update(['action_url_mobile' => null]);

                $course= Course::withTrashed()->where('id', $id)->firstOrFail();
                $course->forceDelete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Packages deleted successfully";
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No Package selected";
        }
        return redirect()->back()->with('notification', $notification);
    }

    public function check_coursePackages($courseId)
    {
        $course = Course::where('id', $courseId)->withTrashed()->with(['packages.original_course','plans'])->first();
                // dd($course);
                $courses = $course->packages->pluck('original_course');
                // dd($courses);
                if($courses->count() > 0){
                    $status = "true";
                }else{
                    $status = "false";
                }

                return response()->json($status);
    }

    public function get_addable_learners($courseId)

    {
        // dd($courseId);
        // if (!$this->user->can('add_learners')) abort(403);
        $pageSize  = (isset($_GET["length"])) ? $_GET["length"] : 10;
        $start  = (isset($_GET["start"])) ? $_GET["start"] : 0;
        $course = Course::where('id', $courseId)->with(['packages.original_course.plans' => function ($query) {
            return $query->where('status', 1);
        }])->first();
        $plans = (isset($course->packages->first()->original_course) && !empty($course->packages->first()->original_course))?$course->packages->first()->original_course->plans->isNotEmpty():null;
        // $query = Learner::with(['user_courses.course.plans']);
        $query =Learner::distinct();
        //->whereNotIn('id', function ($query) use ($courseId) {
        //     $query->select('learner_id')->distinct()->from('user_courses')->where('course_id', $courseId)->whereNull('expire_at')->orwhereDate('expire_at', '>', Carbon::now());
        // })
        // ->get();

        if (isset($_GET["search"]["value"]) && !empty($_GET["search"]["value"])) {
            $searchValue = $_GET["search"]["value"];
            $query->where(function ($q) use ($searchValue) {
                $q->where('learners.name', 'like', "%$searchValue%")
                    ->orWhere('learners.email', 'like', "%$searchValue%")
                    ->orWhere('learners.mobile', 'like', "%$searchValue%");
            });
        }

        $count_record = count($query->get());
        $data = $query->skip($start)->take($pageSize);
            // dd("");
        return DataTables::of($data)->with([
            "recordsTotal"=>$count_record,
            "recordsFiltered"=>$count_record
        ])
            // return DataTables::of($query)
                ->addColumn('action', function ($row) use ($course, $plans) {
                    $url = route("delete_addable.learners", ["id" => $row->id]);
                    $button = "";
                    if ($this->user->can('add_learners')) {
                        $addurl = route('package.addable_learners', ['packageId' => $course->id, 'learnerId' => $row->id]);
                        $show_plan = '';
                        if ($plans) {
                            $href = $addurl;
                        } else {
                            $href = "javascript:void(0)";
                            // $show_plan = "show_plan(event,'" . $course->id . "','" . $row->id . "')";
                        }
                        $show_plan="show_plan(event,'".$course->id."','".$row->id."','true')";
                        if( Auth::user()->hasPermissionTo('add_enroll') ) {
                        $button .= '<a class="mx-1 text-success" href="' . $href . '" title="Add" data-url="' . $addurl . '" data-courseId="' . $course->id . '" data-learnerId="' . $row->id . '" onclick="' . $show_plan . '"><i class="fas fa-plus-square"></i></a>';
                        }
                    }
                    return "<div class='d-flex justify-content-center'>$button</div>";
                })
                ->editColumn('created_at', function ($row) {
                    return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
                })
                ->rawColumns(['created_at', 'action'])
                ->addIndexColumn()
                ->toJson();

    }

    public function addable_learners(Request $request, $courseId,$learnerId,$planId=NULL){

        // dd($learnerId);
        $notification=[];
        $mobile=[];
        $email=[];
        $ids=[];
        $notification=[];
        $learner_count = 0;
        if(!empty($courseId) && !empty($learnerId)){
            $learners = explode(',',$learnerId);
            // dd($learners);
            $learner_count = count($learners);
            if($learner_count > 0){
                $course = Course::where('id', $courseId)->withTrashed()->with(['packages.original_course','plans'])->first();
                // dd($course);
                $courses = $course->packages->pluck('original_course');
                // dd($courses);
                if($courses->count() > 0)
                // dd($learners);
                // dd("ok");
                foreach ($learners as $key => $value) {
                    DB::statement('SET FOREIGN_KEY_CHECKS = 0');
                    $expiredAt = get_plan_expire($planId);
                    $learnerData = Learner::with('country:id,phonecode')->where("id",$value)->get()->first();
                    $ids[] = $learnerData->id;
                    $coursePlanData = CoursePlan::where("id",$planId)->get()->first();
                    $course_title = $course->title;
                    $userCourse = UserCourse::create([
                        'course_id' => $courseId,
                        'learner_id' => $value,
                        'plan_id' => $planId,
                        'order_status' => 1,
                        'expire_at'=> $expiredAt,
                        'price' => (!empty($coursePlanData)) ? $coursePlanData->final_payable_price : 0.00,
                        'full_name' => (!empty($learnerData)) ? $learnerData->name : NULL,
                        'email' => (!empty($learnerData)) ? $learnerData->email : NULL,
                        'mobile' => (!empty($learnerData)) ? $learnerData->mobile : NULL,
                        'country_id' => (!empty($learnerData)) ? $learnerData->country_id : NULL,
                        'state_id' => (!empty($learnerData)) ? $learnerData->state_id : NULL,
                        'city_id' => (!empty($learnerData)) ? $learnerData->city_id : NULL
                    ]);
                    $user_course_id = $userCourse->id;
                    $email[]=$learnerData->email;
                    $mobile[]=$learnerData->mobile;
                    $content = "You are enrolled in Package ".$course_title;
                    $subject = "Enrolled in Package";
                    $params['common']=[
                        'course_id' => $courseId,
                        'learner_id' => $learnerData->id,
                        'type'=> 3
                    ];
                    $params['email']['user_course_id'] = $user_course_id;
                    $params['email']['to'] = $email;
                    $params['push']['to'] = implode(",",$ids);
                    $params['web']['to'] = $ids;
                    // need to append country code with phone number dynamically
                    $params['whatsapp']['to'] = $learnerData->country->phonecode."".$learnerData->mobile;//"919898222366";
                    $params['whatsapp']['message'] = $course_title;//"You are enrolled in course ".
                    $params['whatsapp']['template'] = 'course_purchased';
                    send_notification($params,'enroll',['whatsapp','email','push','web'],$subject,$content);
                    // UserCourse::create([
                    //     'course_id' => $courseId,
                    //     'learner_id' => $value,
                    //     'plan_id' => $planId,
                    //     'order_status' => 1
                    // ]);
                    // foreach ($courses as $key1 => $value1) {
                    // }

                    DB::statement('SET FOREIGN_KEY_CHECKS = 1');
                }
            }
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Learner added to the package successfully";
        if($learner_count > 1){
            $notification['msg'] = "Learners added to the course successfully";
        }
        return redirect()->route("packages.learners", ['id' => $courseId])->with('notification', $notification);
    }

    public function askforApprovalPackages(Request $request)
    {


        if (!$this->user->can('add_packages')) abort(403);
        $notification = [];
        // $validated = $request->validate([
        //     // 'subject' => 'required|filled|regex:/^[a-zA-ZÑñ\s]+$/|max:255',
        //     'subject' => 'required|filled',
        //     'description'=>['required','filled'],
        // ]);
        $users = User::role('admin')->select(['id','name','email'])->get();
        $course = Course::findOrFail($request->course_id);

        if (!empty($course)) {
            Course::where('id', $request->course_id)->update(['approval_status' => "1"]);
        }


        $subject = "Support ticket mail send";
        $instructor = User::select(['id','name','email'])->where('id',auth()->id())->firstOrFail();
        $support = Support::create([
            'subject' => allowWhiteSpace($instructor->name. ' has aksed for ' . $course->title .' approval'),
            'description' => 'Please check course and approve or reply from support ticket in admin',
            'created_by'=> auth()->id()
        ]);

        $url = route('support-ticket.index');
        if(isset($users) && !empty($users)){
            foreach($users as $user){
                Mail::to($user->email)->send(new SupportTicketCreatedMail($user, $instructor, $support, $url , $subject));
            }
        }

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Sent request for approval successfully";
        return redirect('backoffice/packages')->with('notification', $notification);
    }

}
